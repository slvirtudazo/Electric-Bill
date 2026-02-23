<?php
session_start();

// Check if the form has been submitted
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // Retrieve and validate user inputs
    $name = htmlspecialchars(trim($_POST['name']));
    $previous = floatval($_POST['previous']);
    $current = floatval($_POST['current']);
    $customerTypeVal = $_POST['mySelect'];

    // Save inputs in session to repopulate the form after reload
    $_SESSION['inputs'] = [
        'name' => $name,
        'previous' => $_POST['previous'],
        'current' => $_POST['current'],
        'mySelect' => $customerTypeVal,
    ];

    // Ensure a customer type is selected
    if ($customerTypeVal === "0") {
        $_SESSION['result'] = [
            'type' => 'error',
            'message' => "<strong>Error:</strong> Please select a valid customer type."
        ];

        // Ensure current reading not less than the previous reading
    } elseif ($current < $previous) {
        $_SESSION['result'] = [
            'type' => 'error',
            'message' => "<strong>Invalid Reading:</strong> Current reading cannot be lower than previous."
        ];

        // If validation passes, calculates the total usage and rate per kWh
    } else {
        $usage = $current - $previous;
        $rate = ($usage <= 200) ? 10.00 : 15.00;

        // Apply surcharge and label based on customer type
        if ($customerTypeVal === "2") {
            $customerTypeName = "Commercial (+ &#8369;500)";
            $surcharge = 500.00;
        } else {
            $customerTypeName = "Residential";
            $surcharge = 0.00;
        }

        // Calculate the final bill amount
        $baseCost = $usage * $rate;
        $totalBill = $baseCost + $surcharge;

        // Store the calculation details in session
        $_SESSION['result'] = [
            'type' => 'success',
            'name' => $name,
            'custType' => $customerTypeName,
            'usage' => number_format($usage, 2),
            'rate' => number_format($rate, 2),
            'total' => number_format($totalBill, 2),
        ];
    }

    // Redirect back to the same page to prevent duplicate form submissions
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Retrieve session data using null coalescing, setting defaults if empty
$result = $_SESSION['result'] ?? null;
$inputs = $_SESSION['inputs'] ?? ['name' => '', 'previous' => '', 'current' => '', 'mySelect' => '0'];

unset($_SESSION['result']);

// Only clear the form inputs from the session if the last calculation was successful
if ($result !== null && $result['type'] === 'success') {
    unset($_SESSION['inputs']);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Eco-Friendly Electric Bill App</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>

    <div class="wrapper">

        <div class="card">
            <h2>Eco-Friendly Electric Bill App</h2>

            <form action="" method="POST">
                <div class="field-group">
                    <label for="name">Consumer Name</label>
                    <input type="text" id="name" name="name" placeholder="Enter your name" value="<?php echo $inputs['name']; ?>" required>
                </div>

                <div class="field-group">
                    <label for="previous">Previous Reading (kWh)</label>
                    <input type="number" id="previous" name="previous" placeholder="Enter previous reading" min="0" step="0.01" value="<?php echo $inputs['previous']; ?>" required>
                </div>

                <div class="field-group">
                    <label for="current">Current Reading (kWh)</label>
                    <input type="number" id="current" name="current" placeholder="Enter current reading" min="0" step="0.01" value="<?php echo $inputs['current']; ?>" required>
                </div>

                <div class="field-group">
                    <label for="mySelect">Customer Type</label>
                    <select id="mySelect" name="mySelect" required>
                        <option value="0" <?php echo $inputs['mySelect'] === '0' ? 'selected' : ''; ?>>Select customer type</option>
                        <option value="1" <?php echo $inputs['mySelect'] === '1' ? 'selected' : ''; ?>>Residential</option>
                        <option value="2" <?php echo $inputs['mySelect'] === '2' ? 'selected' : ''; ?>>Commercial (+ &#8369;500)</option>
                    </select>
                </div>

                <div class="btn-group">
                    <button type="submit">Calculate Bill</button>
                </div>
            </form>
        </div>

        <div class="card">
            <h2>Statement of Account</h2>

            <?php if ($result === null): ?>
                <div class="soa-placeholder">
                    <p>Complete the form to view your statement here.</p>
                </div>

            <?php elseif ($result['type'] === 'error'): ?>
                <div class="result-box error-msg">
                    <?php echo $result['message']; ?>
                </div>

            <?php else: ?>
                <div class="result-box success-msg">
                    <p><span class="label">Consumer Name:</span> <?php echo $result['name']; ?></p>
                    <p><span class="label">Customer Type:</span> <?php echo $result['custType']; ?></p>
                    <p><span class="label">Usage (kWh):</span> <?php echo $result['usage']; ?> kWh</p>
                    <p><span class="label">Rate per kWh:</span> &#8369;<?php echo $result['rate']; ?></p>
                    <hr>
                    <p><span class="label">Total Bill:</span> &#8369;<?php echo $result['total']; ?></p>
                </div>
            <?php endif; ?>

        </div>
    </div>

</body>

</html>