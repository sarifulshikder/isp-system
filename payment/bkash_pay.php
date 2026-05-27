<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$base_path = '../';

include $base_path . 'config.php';
include $base_path . 'includes/auth.php';
include $base_path . 'config/bkash_config.php';

$page_title = "Payment - bKash";

include $base_path . 'includes/sidebar.php';
include $base_path . 'includes/header.php';

$username = $_SESSION['username'] ?? '';
?>

<div class="main">
    <div class="table-box">
        <div class="table-header">
            <h3><i class="fa fa-wallet"></i> Recharge Wallet via bKash</h3>
        </div>
        
        <div style="max-width: 500px; margin: 0 auto;">
            <div class="form-group">
                <label class="form-label">Your Username</label>
                <input type="text" class="form-control" value="<?= htmlspecialchars($username) ?>" readonly>
            </div>
            
            <div class="form-group">
                <label class="form-label">Amount (BDT)</label>
                <input type="number" id="amount" class="form-control" placeholder="Enter amount in BDT" min="10" value="<?= htmlspecialchars($_GET['amount'] ?? '') ?>" required>
            </div>
            
            <button type="button" id="bKash_button" class="btn btn-primary" style="width: 100%; background-color: #e2136e; border-color: #e2136e;">
                <i class="fa fa-credit-card"></i> Pay with bKash
            </button>
            
            <div style="text-align: center; margin-top: 20px; color: var(--text-muted); font-size: 13px;">
                <i class="fa fa-info-circle"></i> Minimum amount: BDT 10
            </div>
        </div>
    </div>
    
    <div class="table-box">
        <div class="table-header">
            <h3><i class="fa fa-history"></i> Recent Transactions</h3>
        </div>
        
        <?php
        $transactions = $conn->query("
            SELECT * FROM wallet_transactions 
            WHERE username = '$username' AND gateway = 'bkash'
            ORDER BY created_at DESC 
            LIMIT 10
        ");
        ?>
        
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Amount (BDT)</th>
                    <th>Status</th>
                    <th>Transaction ID</th>
                </tr>
            </thead>
            <tbody>
                <?php if($transactions && $transactions->num_rows > 0): ?>
                    <?php while($t = $transactions->fetch_assoc()): ?>
                        <tr>
                            <td><?= date('Y-m-d H:i', strtotime($t['created_at'])) ?></td>
                            <td><?= number_format($t['amount'], 2) ?></td>
                            <td>
                                <?php if($t['status'] == 'success'): ?>
                                    <span class="badge active">Success</span>
                                <?php elseif($t['status'] == 'pending'): ?>
                                    <span class="badge warning">Pending</span>
                                <?php else: ?>
                                    <span class="badge expired">Failed</span>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($t['txn_id'] ?? 'N/A') ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" style="text-align: center; color: var(--text-muted);">
                            No transactions yet
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- bKash Checkout Script -->
<script id="myScript" src="https://scripts.sandbox.bka.sh/versions/1.2.0-beta/checkout/bKash-checkout-sandbox.js"></script>

<script>
    var accessToken = '';

    $(document).ready(function () {
        $.ajax({
            url: "bkash_token.php",
            type: 'POST',
            contentType: 'application/json',
            success: function (data) {
                console.log('got data from token  ..');
                console.log(JSON.stringify(data));
                var obj = JSON.parse(data);
                accessToken = obj.id_token;
            },
            error: function () {
                console.log('error');
            }
        });

        var paymentConfig = {
            createCheckoutURL: "bkash_create.php",
            executeCheckoutURL: "bkash_execute.php",
        };

        var paymentRequest = {
            amount: $('#amount').val(),
            intent: 'sale'
        };

        bKash.init({
            paymentMode: 'checkout',
            paymentRequest: paymentRequest,
            createRequest: function (request) {
                var amount = $('#amount').val();
                if(!amount || amount < 10) {
                    alert('Please enter valid amount (minimum BDT 10)');
                    bKash.create().onError();
                    return;
                }
                
                $.ajax({
                    url: paymentConfig.createCheckoutURL + "?amount=" + amount,
                    type: 'GET',
                    contentType: 'application/json',
                    success: function (data) {
                        var obj = JSON.parse(data);
                        if (data && obj.paymentID != null) {
                            paymentID = obj.paymentID;
                            bKash.create().onSuccess(obj);
                        } else {
                            bKash.create().onError();
                        }
                    },
                    error: function () {
                        bKash.create().onError();
                    }
                });
            },
            executeRequest: function () {
                $.ajax({
                    url: paymentConfig.executeCheckoutURL + "?paymentID=" + paymentID,
                    type: 'GET',
                    contentType: 'application/json',
                    success: function (data) {
                        data = JSON.parse(data);
                        if (data && data.paymentID != null) {
                            window.location.href = "bkash_verify.php?paymentID=" + data.paymentID + "&trxID=" + data.trxID + "&amount=" + data.amount;
                        } else {
                            alert(data.errorMessage);
                            bKash.execute().onError();
                        }
                    },
                    error: function () {
                        bKash.execute().onError();
                    }
                });
            },
            onClose: function () {
                console.log("bKash widget closed");
            }
        });

        $('#bKash_button').click(function() {
            bKash.show().init();
        });
    });
</script>

<?php include $base_path . 'includes/footer.php'; ?>
