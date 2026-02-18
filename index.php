<?php

session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    /* Validate and retrieve user inputs */
    $name            = htmlspecialchars(trim($_POST['name']));
    $previous        = floatval($_POST['previous']);
    $current         = floatval($_POST['current']);
    $customerTypeVal = $_POST['mySelect'];

    /* Store inputs in session to repopulate form later */
    $_SESSION['inputs'] = [
        'name'     => $name,
        'previous' => $_POST['previous'],
        'current'  => $_POST['current'],
        'mySelect' => $customerTypeVal,
    ];

    /* Validate: Ensure a customer type is selected */
    if ($customerTypeVal == "0") {
        $_SESSION['result'] = [
            'type'    => 'error',
            'message' => "<strong>Error:</strong> Please select a valid customer type."
        ];

        /* Validate: Ensure current reading is not less than previous reading */
    } elseif ($current < $previous) {
        $_SESSION['result'] = [
            'type'    => 'error',
            'message' => "<strong>Invalid Reading:</strong> Current reading cannot be lower than previous."
        ];
    } else {

        /* Calculate total usage (kWh) */
        $usage = $current - $previous;

        /* Determine rate based on usage (200 kWh) */
        $rate = ($usage <= 200) ? 10.00 : 15.00;

        /* Apply surcharge for commercial customers */
        if ($customerTypeVal === "2") {
            $customerTypeName = "Commercial (+ &#8369;500)";
            $surcharge        = 500.00;
        } else {
            $customerTypeName = "Residential";
            $surcharge        = 0.00;
        }

        /* Compute base cost and final total bill */
        $baseCost  = $usage * $rate;
        $totalBill = $baseCost + $surcharge;

        /* Save calculation details for display */
        $_SESSION['result'] = [
            'type'      => 'success',
            'name'      => $name,
            'custType'  => $customerTypeName,
            'usage'     => number_format($usage, 2),
            'rate'      => number_format($rate, 2),
            'base'      => number_format($baseCost, 2),
            'surcharge' => number_format($surcharge, 2),
            'total'     => number_format($totalBill, 2),
        ];
    }

    /* Redirect to self to prevent form resubmission */
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

/* Retrieve session data for display, then clear session to reset state */
$result = null;
$inputs = ['name' => '', 'previous' => '', 'current' => '', 'mySelect' => '0'];

if (isset($_SESSION['result'])) {
    $result = $_SESSION['result'];
    unset($_SESSION['result']);
}

if (isset($_SESSION['inputs'])) {
    $inputs = $_SESSION['inputs'];
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
                    <input type="text" id="name" name="name"
                        placeholder="Enter your name"
                        value="<?php echo $inputs['name']; ?>" required>
                </div>

                <div class="field-group">
                    <label for="previous">Previous Reading (kWh)</label>
                    <input type="number" id="previous" name="previous"
                        placeholder="Enter previous reading"
                        min="0" step="0.01"
                        value="<?php echo $inputs['previous']; ?>" required>
                </div>

                <div class="field-group">
                    <label for="current">Current Reading (kWh)</label>
                    <input type="number" id="current" name="current"
                        placeholder="Enter current reading"
                        min="0" step="0.01"
                        value="<?php echo $inputs['current']; ?>" required>
                </div>

                <div class="field-group">
                    <label for="mySelect">Customer Type</label>
                    <select id="mySelect" name="mySelect" required>
                        <option value="0" <?php echo $inputs['mySelect'] == '0' ? 'selected' : ''; ?>>Select customer type</option>
                        <option value="1" <?php echo $inputs['mySelect'] == '1' ? 'selected' : ''; ?>>Residential</option>
                        <option value="2" <?php echo $inputs['mySelect'] == '2' ? 'selected' : ''; ?>>Commercial (+ &#8369;500)</option>
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