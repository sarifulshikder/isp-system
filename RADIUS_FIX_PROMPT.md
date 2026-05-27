# FreeRADIUS SQL Authentication Fix — Supervision Prompt

## Context
আমার একটা Docker-based ISP Management System আছে। FreeRADIUS MySQL থেকে user authenticate করতে পারছে না।

## System Info
- OS: Ubuntu 24, Docker
- FreeRADIUS: 3.2.8 (container: isp_radius)
- MySQL: 8.0.46 (container: isp_db)
- Network: isp-platform-as_isp_network
- Config volume: ~/ISP-PLATFORM-AS/radius/config → /etc/freeradius (container)
- Log volume: ~/ISP-PLATFORM-AS/radius/logs → /var/log/freeradius (container)

## DB Credentials
- Host: db (Docker DNS)
- User: isp_user
- Pass: isp_pass123
- DB: isp_db

## Current Status
✅ FreeRADIUS container চলছে (isp_radius)
✅ MySQL connected (rlm_sql_mysql Connected to database 'isp_db')
✅ clients.conf ঠিক আছে (localhost secret=123456, Cudy, RB1100)
✅ files module থেকে authenticate হয় (Access-Accept আসে)
✅ radcheck table-এ user আছে:
   - username: testppp, attribute: Cleartext-Password, op: :=, value: test123
❌ SQL module থেকে authenticate হয় না ("No Auth-Type found")
❌ authorize_check_query কখনো execute হয় না

## Current Config Files

### /etc/freeradius/mods-enabled/sql (working MySQL config):
```
sql {
    dialect = "mysql"
    driver = "rlm_sql_mysql"
    server = "db"
    port = 3306
    login = "isp_user"
    password = "isp_pass123"
    radius_db = "isp_db"
    sql_user_name = "%{User-Name}"
    acct_table1 = "radacct"
    acct_table2 = "radacct"
    postauth_table = "radpostauth"
    authcheck_table = "radcheck"
    authreply_table = "radreply"
    groupcheck_table = "radgroupcheck"
    groupreply_table = "radgroupreply"
    usergroup_table = "radusergroup"
    group_membership_query = "SELECT groupname FROM radusergroup WHERE username = '%{SQL-User-Name}' ORDER BY priority"
    read_clients = no
    client_table = "nas"
    pool {
        start = 5
        min = 4
        max = 10
        spare = 3
        uses = 0
        lifetime = 0
        idle_timeout = 60
    }
}
```

### /etc/freeradius/sites-enabled/default (current):
```
server default {
    authorize {
        preprocess
        chap
        mschap
        digest
        suffix
        eap { ok = return }
        files
        sql
        expiration
        logintime
        pap
    }
    authenticate {
        Auth-Type PAP { pap }
        Auth-Type CHAP { chap }
        Auth-Type MS-CHAP { mschap }
        digest
        eap
    }
    preacct {
        preprocess
        acct_unique
        suffix
        files
    }
    accounting {
        detail
        sql
        exec
        attr_filter.accounting_response
    }
    post-auth {
        update { &reply: += &session-state: }
        sql
        exec
        remove_reply_message_if_eap
        Post-Auth-Type REJECT {
            sql
            attr_filter.access_reject
            eap
            remove_reply_message_if_eap
        }
    }
}
```

### /etc/freeradius/mods-config/sql/main/mysql/queries.conf:
```
authorize_check_query = "\
        SELECT id, username, attribute, value, op \
        FROM ${authcheck_table} \
        WHERE username = '%{SQL-User-Name}' \
        ORDER BY id"

authorize_reply_query = "\
        SELECT id, username, attribute, value, op \
        FROM ${authreply_table} \
        WHERE username = '%{SQL-User-Name}' \
        ORDER BY id"

group_membership_query = "\
        SELECT groupname \
        FROM ${usergroup_table} \
        WHERE username = '%{SQL-User-Name}' \
        ORDER BY priority"

authorize_group_check_query = "\
        SELECT id, groupname, attribute, value, op \
        FROM ${groupcheck_table} \
        WHERE groupname = '%{${group_attribute}}' \
        ORDER BY id"

authorize_group_reply_query = "\
        SELECT id, groupname, attribute, value, op \
        FROM ${groupreply_table} \
        WHERE groupname = '%{${group_attribute}}' \
        ORDER BY id"

post-auth_query = "\
        INSERT INTO ${postauth_table} \
        (username, pass, reply, authdate) \
        VALUES ('%{SQL-User-Name}', \
        '%{%{User-Password}:-%{Chap-Password}}', \
        '%{reply:Packet-Type}', '%S')"

accounting_start_query = "\
        INSERT INTO ${acct_table1} \
        (acctsessionid, acctuniqueid, username, nasipaddress, nasportid, \
        nasporttype, acctstarttime, acctauthentic, framedipaddress, \
        acctinputoctets, acctoutputoctets, servicetype, framedprotocol) \
        VALUES ('%{Acct-Session-Id}', '%{Acct-Unique-Session-Id}', \
        '%{SQL-User-Name}', '%{NAS-IP-Address}', '%{NAS-Port-Id}', \
        '%{NAS-Port-Type}', FROM_UNIXTIME(%{integer:Event-Timestamp}), \
        '%{Acct-Authentic}', '%{Framed-IP-Address}', '0', '0', \
        '%{Service-Type}', '%{Framed-Protocol}')"

accounting_update_query = "\
        UPDATE ${acct_table1} \
        SET framedipaddress = '%{Framed-IP-Address}', \
        acctsessiontime = '%{integer:Acct-Session-Time}', \
        acctinputoctets = '%{integer:Acct-Input-Octets}', \
        acctoutputoctets = '%{integer:Acct-Output-Octets}' \
        WHERE acctsessionid = '%{Acct-Session-Id}' \
        AND username = '%{SQL-User-Name}' \
        AND nasipaddress = '%{NAS-IP-Address}'"

accounting_stop_query = "\
        UPDATE ${acct_table2} \
        SET acctstoptime = FROM_UNIXTIME(%{integer:Event-Timestamp}), \
        acctsessiontime = '%{integer:Acct-Session-Time}', \
        acctinputoctets = '%{integer:Acct-Input-Octets}', \
        acctoutputoctets = '%{integer:Acct-Output-Octets}', \
        acctterminatecause = '%{Acct-Terminate-Cause}' \
        WHERE acctsessionid = '%{Acct-Session-Id}' \
        AND username = '%{SQL-User-Name}' \
        AND nasipaddress = '%{NAS-IP-Address}'"

accounting_onoff_query = "\
        UPDATE ${acct_table1} \
        SET acctstoptime = FROM_UNIXTIME(%{integer:Event-Timestamp}), \
        acctterminatecause = '%{Acct-Terminate-Cause}' \
        WHERE acctstoptime IS NULL \
        AND nasipaddress = '%{NAS-IP-Address}'"
```

## Debug Findings
1. `docker exec isp_radius radtest testppp test123 127.0.0.1 1812 123456` → Access-Reject
2. Error log: `No Auth-Type found: rejecting the user via Post-Auth-Type = Reject`
3. Debug log-এ `authorize_check_query` কখনো execute হয় না
4. Debug log-এ শুধু `group_membership_query` execute হয় (user not found in groups)
5. `files` module-এ user রাখলে Access-Accept আসে
6. SQL MySQL-এ connected (confirmed)
7. `isp_user` এর SELECT permission আছে radcheck table-এ (confirmed)

## What Needs to Be Fixed
FreeRADIUS SQL module authorize section-এ `authorize_check_query` execute করে radcheck table থেকে user password retrieve করতে হবে। তারপর PAP দিয়ে authenticate হবে।

## Test Command
```bash
docker exec isp_radius radtest testppp test123 127.0.0.1 1812 123456
```
Success হলে `Access-Accept` আসবে।

## Important Notes
- Config files volume mount হওয়া: ~/ISP-PLATFORM-AS/radius/config → /etc/freeradius
- Container-এ সরাসরি file edit করা যায়, কিন্তু restart-এ lost হয়
- Host-এ ~/ISP-PLATFORM-AS/radius/config/ এ edit করলে persistent থাকে
- কিছু file root-owned, sudo দরকার
- Docker restart: `docker restart isp_radius`
- Log: `sudo tail -f ~/ISP-PLATFORM-AS/radius/logs/radius.log`
