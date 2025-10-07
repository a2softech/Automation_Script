<?php
date_default_timezone_set('Asia/Kolkata'); // IST

// index.php
$jsonFile = 'balances.json';

// Agar JSON file exist nahi karti to default create karo
if (!file_exists($jsonFile)) {
    $defaultData = [
        'last_updated' => date('Y-m-d H:i:s'),
        'accounts' => [
            ['name' => 'HDFC Bank', 'balance' => 25000.50],
            ['name' => 'SBI Bank', 'balance' => 18000.75],
            ['name' => 'ICICI Bank', 'balance' => 32000.00]
        ],
        'credit_cards' => [
            ['card_name' => 'HDFC Credit Card', 'due_amount' => 15000.25, 'due_date' => '2025-10-15'],
            ['card_name' => 'SBI Credit Card', 'due_amount' => 8000.00, 'due_date' => '2025-10-22']
        ],
        // Naye sections
        'receivables' => [ // jinse paise lena hai (others owe you)
            ['name' => 'Ramesh', 'amount' => 5000.50, 'note' => 'Personal loan']
        ],
        'payables' => [ // jinhe paise dena hai (you owe others)
            ['name' => 'ABC Store', 'amount' => 2300.75, 'note' => 'Electronics']
        ]
    ];
    file_put_contents($jsonFile, json_encode($defaultData, JSON_PRETTY_PRINT));
}

// Load JSON file
$jsonData = file_get_contents($jsonFile);
$data = json_decode($jsonData, true);

// Safety checks & defaults
if (!is_array($data)) {
    $data = ['last_updated' => date('Y-m-d H:i:s'), 'accounts' => [], 'credit_cards' => [], 'receivables' => [], 'payables' => []];
}
if (!isset($data['last_updated'])) $data['last_updated'] = date('Y-m-d H:i:s');
if (!isset($data['accounts']) || !is_array($data['accounts'])) $data['accounts'] = [];
if (!isset($data['credit_cards']) || !is_array($data['credit_cards'])) $data['credit_cards'] = [];
if (!isset($data['receivables']) || !is_array($data['receivables'])) $data['receivables'] = [];
if (!isset($data['payables']) || !is_array($data['payables'])) $data['payables'] = [];

// --- Handle form submission ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Save edits to lists (if update button used)
    if (isset($_POST['update_balances'])) {
        // accounts
        $accounts = [];
        if (isset($_POST['account_name']) && is_array($_POST['account_name'])) {
            foreach ($_POST['account_name'] as $i => $name) {
                $accounts[] = [
                    'name' => trim($name),
                    'balance' => floatval($_POST['account_balance'][$i] ?? 0)
                ];
            }
        }
        $data['accounts'] = $accounts;

        // credit cards
        $cards = [];
        if (isset($_POST['card_name']) && is_array($_POST['card_name'])) {
            foreach ($_POST['card_name'] as $i => $cname) {
                $cards[] = [
                    'card_name' => trim($cname),
                    'due_amount' => floatval($_POST['card_due'][$i] ?? 0),
                    'due_date' => $_POST['card_date'][$i] ?? ''
                ];
            }
        }
        $data['credit_cards'] = $cards;

        // receivables
        $rec = [];
        if (isset($_POST['rec_name']) && is_array($_POST['rec_name'])) {
            foreach ($_POST['rec_name'] as $i => $rname) {
                $rec[] = [
                    'name' => trim($rname),
                    'amount' => floatval($_POST['rec_amount'][$i] ?? 0),
                    'note' => trim($_POST['rec_note'][$i] ?? '')
                ];
            }
        }
        $data['receivables'] = $rec;

        // payables
        $pay = [];
        if (isset($_POST['pay_name']) && is_array($_POST['pay_name'])) {
            foreach ($_POST['pay_name'] as $i => $pname) {
                $pay[] = [
                    'name' => trim($pname),
                    'amount' => floatval($_POST['pay_amount'][$i] ?? 0),
                    'note' => trim($_POST['pay_note'][$i] ?? '')
                ];
            }
        }
        $data['payables'] = $pay;

        $data['last_updated'] = date('Y-m-d H:i:s');
    }

    // Add new bank
    if (isset($_POST['add_bank'])) {
        $data['accounts'][] = ['name' => $_POST['new_bank_name'], 'balance' => floatval($_POST['new_bank_balance'])];
        $data['last_updated'] = date('Y-m-d H:i:s');
    }

    // Add new card
    if (isset($_POST['add_card'])) {
        $data['credit_cards'][] = [
            'card_name' => $_POST['new_card_name'],
            'due_amount' => floatval($_POST['new_card_due']),
            'due_date' => $_POST['new_card_date']
        ];
        $data['last_updated'] = date('Y-m-d H:i:s');
    }

    // Add receivable
    if (isset($_POST['add_rec'])) {
        $data['receivables'][] = [
            'name' => $_POST['new_rec_name'],
            'amount' => floatval($_POST['new_rec_amount']),
            'note' => $_POST['new_rec_note'] ?? ''
        ];
        $data['last_updated'] = date('Y-m-d H:i:s');
    }

    // Add payable
    if (isset($_POST['add_pay'])) {
        $data['payables'][] = [
            'name' => $_POST['new_pay_name'],
            'amount' => floatval($_POST['new_pay_amount']),
            'note' => $_POST['new_pay_note'] ?? ''
        ];
        $data['last_updated'] = date('Y-m-d H:i:s');
    }

    // Save file and redirect (PRG)
    file_put_contents($jsonFile, json_encode($data, JSON_PRETTY_PRINT));
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Totals (decimals kept)
$totalBalance = array_sum(array_column($data['accounts'], 'balance'));
$totalDue = array_sum(array_column($data['credit_cards'], 'due_amount'));
$totalReceivable = array_sum(array_column($data['receivables'], 'amount'));
$totalPayable = array_sum(array_column($data['payables'], 'amount'));
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Bank & Credit Card + Loans Dashboard</title>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
    .card > div {
        border: 1px solid #d4d9dfff; padding: 8px; border-radius: 8px; margin-bottom: 6px;
    }
    body {font-family: 'Segoe UI', sans-serif; margin: 0; background: #f7f9fb; color: #333;}
    .container {max-width: 1200px; margin: auto; padding: 20px;}
    h1 {text-align: center; color: #2c3e50;}
    .grid-forms {display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 15px; margin-top: 20px;}
    .card {background: #fff; border-radius: 12px; padding: 16px; box-shadow: 0 3px 8px rgba(0,0,0,0.06);}
    .card h3 {margin: 0 0 10px;}
    input[type="text"], input[type="number"], input[type="date"] {width: 90%; padding: 8px; margin: 6px 0; border-radius: 6px; border: 1px solid #ccc;}
    button {background: #2ecc71; color: #fff; padding: 8px 12px; border: none; border-radius: 8px; cursor: pointer;}
    .charts {display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 16px; margin-top: 24px;}
    .chart-box {text-align: center; background: #fff; border-radius: 12px; padding: 12px; box-shadow:0 2px 6px rgba(0,0,0,0.05);}
    .total-amount {font-size: 18px; font-weight: 700; margin-bottom: 6px;}
    .muted {color:#6b7280;font-size:0.9rem}
    @media(max-width:600px){ .container{padding:12px} }
    .small-note {font-size:0.85rem;color:#475569}
</style>
</head>
<body>
<div class="container">
    <h1>💰 Money Manager — Banks, Cards & Loans</h1>
    <p class="muted">Last Updated: <?= htmlspecialchars($data['last_updated']) ?> (IST)</p>

    <form method="POST">
        <h2 class="small-note">Edit lists and then click <b>Save Changes</b></h2>
        <div class="grid-forms">
            <!-- Banks -->
            <div class="card">
                <h3>🏦 Bank Accounts</h3>
                <?php foreach ($data['accounts'] as $i => $acc): ?>
                    <div style="margin-bottom:10px;">
                        <input type="text" name="account_name[]" value="<?= htmlspecialchars($acc['name']) ?>">
                        <input type="number" step="0.01" name="account_balance[]" value="<?= htmlspecialchars($acc['balance']) ?>">
                    </div>
                <?php endforeach; ?>
                <div style="margin-top:8px;">
                    <input type="text" name="new_bank_name" placeholder="New bank name">
                    <input type="number" step="0.01" name="new_bank_balance" placeholder="Initial balance">
                    <button type="submit" name="add_bank">Add Bank</button>
                </div>
            </div>

            <!-- Credit Cards -->
            <div class="card">
                <h3>💳 Credit Cards</h3>
                <?php foreach ($data['credit_cards'] as $i => $card): ?>
                    <div style="margin-bottom:10px;">
                        <input type="text" name="card_name[]" value="<?= htmlspecialchars($card['card_name']) ?>">
                        <input type="number" step="0.01" name="card_due[]" value="<?= htmlspecialchars($card['due_amount']) ?>">
                        <input type="date" name="card_date[]" value="<?= htmlspecialchars($card['due_date']) ?>">
                    </div>
                <?php endforeach; ?>
                <div style="margin-top:8px;">
                    <input type="text" name="new_card_name" placeholder="New card name">
                    <input type="number" step="0.01" name="new_card_due" placeholder="Due amount">
                    <input type="date" name="new_card_date" placeholder="Due date">
                    <button type="submit" name="add_card">Add Card</button>
                </div>
            </div>

            <!-- Receivables (jinse paise lena hai) -->
            <div class="card">
                <h3>🤝 Receivables (Jinse paise lena hai)</h3>
                <?php foreach ($data['receivables'] as $i => $r): ?>
                    <div style="margin-bottom:10px;">
                        <input type="text" name="rec_name[]" value="<?= htmlspecialchars($r['name']) ?>" placeholder="Name">
                        <input type="number" step="0.01" name="rec_amount[]" value="<?= htmlspecialchars($r['amount']) ?>" placeholder="Amount">
                        <input type="text" name="rec_note[]" value="<?= htmlspecialchars($r['note'] ?? '') ?>" placeholder="Note (optional)">
                    </div>
                <?php endforeach; ?>
                <div style="margin-top:8px;">
                    <input type="text" name="new_rec_name" placeholder="Name">
                    <input type="number" step="0.01" name="new_rec_amount" placeholder="Amount">
                    <input type="text" name="new_rec_note" placeholder="Note (optional)">
                    <button type="submit" name="add_rec">Add Receivable</button>
                </div>
            </div>

            <!-- Payables (jinhe paise dene hai) -->
            <div class="card">
                <h3>💸 Payables (Jinhe paise dena hai)</h3>
                <?php foreach ($data['payables'] as $i => $p): ?>
                    <div style="margin-bottom:10px;">
                        <input type="text" name="pay_name[]" value="<?= htmlspecialchars($p['name']) ?>" placeholder="Name">
                        <input type="number" step="0.01" name="pay_amount[]" value="<?= htmlspecialchars($p['amount']) ?>" placeholder="Amount">
                        <input type="text" name="pay_note[]" value="<?= htmlspecialchars($p['note'] ?? '') ?>" placeholder="Note (optional)">
                    </div>
                <?php endforeach; ?>
                <div style="margin-top:8px;">
                    <input type="text" name="new_pay_name" placeholder="Name">
                    <input type="number" step="0.01" name="new_pay_amount" placeholder="Amount">
                    <input type="text" name="new_pay_note" placeholder="Note (optional)">
                    <button type="submit" name="add_pay">Add Payable</button>
                </div>
            </div>
        </div>

        <div style="margin-top:14px;text-align:right;">
            <button type="submit" name="update_balances">💾 Save Changes</button>
        </div>
    </form>

    <!-- Charts -->
    <div class="charts">
        <div class="chart-box card">
            <div class="total-amount">🏦 Total Bank Balance: ₹<?= number_format($totalBalance, 2) ?></div>
            <canvas id="bankChart"></canvas>
            <div class="muted">Distribution across bank accounts</div>
        </div>

        <div class="chart-box card">
            <div class="total-amount">💳 Total Card Due: ₹<?= number_format($totalDue, 2) ?></div>
            <canvas id="cardChart"></canvas>
            <div class="muted">Credit card dues</div>
        </div>

        <div class="chart-box card">
            <div class="total-amount">🤝 Total Receivables: ₹<?= number_format($totalReceivable, 2) ?></div>
            <canvas id="recvChart"></canvas>
            <div class="muted">Jinse paise lena hai</div>
        </div>

        <div class="chart-box card">
            <div class="total-amount">💸 Total Payables: ₹<?= number_format($totalPayable, 2) ?></div>
            <canvas id="payChart"></canvas>
            <div class="muted">Jinhe paise dene hai</div>
        </div>
    </div>

</div>

<script>
// Prepare data (server-side embedded)
const accounts = <?= json_encode($data['accounts']); ?>;
const cards = <?= json_encode($data['credit_cards']); ?>;
const receivables = <?= json_encode($data['receivables']); ?>;
const payables = <?= json_encode($data['payables']); ?>;

function makeColors(n) {
    const palette = ['#3498db','#1abc9c','#9b59b6','#f39c12','#e74c3c','#16a085','#e67e22','#8e44ad','#2ecc71','#d35400'];
    const out = [];
    for (let i=0;i<n;i++) out.push(palette[i % palette.length]);
    return out;
}

// Bank chart
new Chart(document.getElementById('bankChart'), {
    type: 'pie',
    data: {
        labels: accounts.map(a => a.name),
        datasets: [{ data: accounts.map(a => parseFloat(a.balance)), backgroundColor: makeColors(accounts.length) }]
    },
    options: { plugins: { title: { display: true, text: 'Bank Balances' } } }
});

// Card chart
new Chart(document.getElementById('cardChart'), {
    type: 'pie',
    data: {
        labels: cards.map(c => c.card_name),
        datasets: [{ data: cards.map(c => parseFloat(c.due_amount)), backgroundColor: makeColors(cards.length) }]
    },
    options: { plugins: { title: { display: true, text: 'Credit Card Dues' } } }
});

// Receivables chart
new Chart(document.getElementById('recvChart'), {
    type: 'pie',
    data: {
        labels: receivables.map(r => r.name),
        datasets: [{ data: receivables.map(r => parseFloat(r.amount)), backgroundColor: makeColors(receivables.length) }]
    },
    options: { plugins: { title: { display: true, text: 'Receivables (Jinse paise lena hai)' } } }
});

// Payables chart
new Chart(document.getElementById('payChart'), {
    type: 'pie',
    data: {
        labels: payables.map(p => p.name),
        datasets: [{ data: payables.map(p => parseFloat(p.amount)), backgroundColor: makeColors(payables.length) }]
    },
    options: { plugins: { title: { display: true, text: 'Payables (Jinhe paise dena hai)' } } }
});
</script>
</body>
</html>
