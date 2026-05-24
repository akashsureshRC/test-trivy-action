<!-- resources/views/popup.blade.php -->
<!-- resources/views/modals/popup.blade.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Popup Page</title>
    <style>
        .module {
            margin: 20px;
            padding: 15px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        .module h2 {
            margin-top: 0;
        }
        .module input {
            margin-bottom: 10px;
            width: 100%;
            padding: 8px;
        }
        .module button {
            padding: 10px 15px;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .module button:hover {
            background-color: #218838;
        }
    </style>
</head>
<body>
    <h1>Popup Page</h1>

    <!-- Income Commission Module -->
    <div class="module">
        <h2>Income </h2>
        
        <p style="cursor: pointer; color: blue;" onclick="openCommissionModal()">Commission</p>

        <!-- Commission Modal -->
        <div id="commissionModal" class="modal">
            <p>You will be prompted on every payslip for the Commission.</p>
            <button class="close-btn" onclick="closeCommissionModal()">Close</button>
        </div>
        <div id="modalOverlay" class="modal-overlay" onclick="closeCommissionModal()"></div>
        <input type="number" id="incomeCommission" placeholder="Enter Income Commission">
        <button onclick="calculatePayslip()">Calculate Payslip</button>
    </div>

    <!-- Loss of Payout Module -->
    <div class="module">
        <h2>Loss of Payout</h2>
        <input type="number" id="lossOfPayout" placeholder="Enter Loss of Payout">
    </div>

    <!-- Payslip Module -->
    <div class="module">
        <h2>Payslip</h2>
        <p><strong>Net Income:</strong> <span id="netIncome">0</span></p>
        <p><strong>Total Deductions:</strong> <span id="totalDeductions">0</span></p>
        <p><strong>Final Payout:</strong> <span id="finalPayout">0</span></p>
    </div>

    <script>
        function calculatePayslip() {
            const incomeCommission = parseFloat(document.getElementById('incomeCommission').value) || 0;
            const lossOfPayout = parseFloat(document.getElementById('lossOfPayout').value) || 0;

            const netIncome = incomeCommission - lossOfPayout;
            const totalDeductions = lossOfPayout;
            const finalPayout = netIncome;

            document.getElementById('netIncome').textContent = netIncome.toFixed(2);
            document.getElementById('totalDeductions').textContent = totalDeductions.toFixed(2);
            document.getElementById('finalPayout').textContent = finalPayout.toFixed(2);
        }
    </script>
      <script>
        function openCommissionModal() {
            document.getElementById('commissionModal').style.display = 'block';
            document.getElementById('modalOverlay').style.display = 'block';
        }

        function closeCommissionModal() {
            document.getElementById('commissionModal').style.display = 'none';
            document.getElementById('modalOverlay').style.display = 'none';
        }
    </script>
</body>
</html>
