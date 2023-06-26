<?php 

require_once '../shared/header.php'; 
require_once './validation.php';

$date    = date('Y-m-d H:i:s');
$day     = date('d', strtotime($date));
$month   = date("F", strtotime($date));
$year    = date("Y", strtotime($date));
$lastday = date('t', strtotime($date));

$cycleData = $amount->GetRemainingDays($parentId);

$remainingDays = 0;
$startDate = '';
$endDate = '';

if (count($cycleData) > 0) {
    $remainingDays = $cycleData[0]["remaining_days"];
    $startDate = date("M d, Y", strtotime($cycleData[0]["start"]));
    $endDate = date("M d, Y", strtotime($cycleData[0]["end"]));
} 

$prevMonth = date("m", strtotime("first day of previous month"));
$prevMonthYear = date("Y", strtotime("first day of previous month"));

$savings = $expenses->GetRecentSavingsAmount($parentId, $prevMonth, $prevMonthYear);
$totalSavings = $expenses->GetTotalSavingsAmount($parentId);
$expensesDetails = $expenses->GetExpensesDetailsByParentId($parentId);
$expensesDetails = array_slice($expensesDetails, 0, 10);
$expensesPercentage = $expenses->GetExpensesByParentId($parentId);
?>

<div class="container-fluid">
    <h3 class="mt-4 text-site-primary text-center">Dashboard</h3>

    <?php if ($wallet[0]['wallet'] < 0): ?>
        <div class="row mt-5">
            <div class="col">
                <div class="alert alert-danger w-100 text-center">
                    <strong class="w-100 d-block">OVER SPENDING!</strong> The system detected that you are spending too much than the budget on your wallet. Kindly adjust your budget to avoid over spending.</a>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <div class="row mt-5">
        <div class="col-lg-4 mb-4">
            <div class="budget-tile h-100 border shadow tile-container">
                <p>My Wallet</p>
                <h3>₱<?php echo number_format($wallet[0]['wallet'] + $cycleAdustedSavings[0]['adjusted_saving'], 2); ?></h3>
                <p class="mt-4">Days Remaining: <?php echo $remainingDays > 0 ? $remainingDays : 0; ?> day(s)</p>
            </div>
        </div>
        <div class="col-lg-4 mb-4">
            <div class="total-budget-tile h-100 border shadow tile-container">
                <p>Total budget this cycle</p>
                <h3 class="text-site-primary">₱<?php echo number_format($budget + $cycleAdustedSavings[0]['adjusted_saving'], 2); ?></h3>

                <?php if ($isCycleStarted === TRUE): ?>
                    <small class="d-block mt-4">Start: <?php echo $startDate; ?></small>
                    <small>End: <?php echo $endDate; ?></small>
                <?php else: ?>
                    <p class="mt-4 text-danger">Cycle is not yet started</p>
                <?php endif; ?>
                
            </div>
        </div>
        <div class="col-lg-4 mb-4">
            <div class="total-savings-tile h-100 border shadow tile-container">
                <p>Total Savings</p>
                <h3 class="text-site-primary">₱<?php echo number_format($totalSavings[0]['savings'], 2); ?></h3>
                <small class="d-block mt-4">Recent Savings</small>
                <strong>₱<?php echo number_format($savings[0]['savings'], 2); ?></strong>
            </div>
        </div>
    </div>

    <div class="row my-5">
        <div class="col">
            <div class="border shadow tile-container">
                <h5 class="text-site-primary mb-4 text-center">Recent Expenses</h5>

                <?php if (count($expensesDetails) < 1): ?>
                    <h5 class="text-center my-5">No data</h5>
                <?php endif; ?>

                <?php 
                    $color = '';
                ?>
                <?php foreach($expensesPercentage as $item): ?>
                    <?php 
                        $count = 0; 
                        $percentage = (int)number_format($item['percentage'], 0);
                        
                        foreach($expensesDetails as $details) {
                            if ($details['category_id'] == $item['id']) $count++;
                        }

                        if ($count < 1) continue;

                        if ($percentage <= 33) $color = 'bg-success';
                        elseif ($percentage <= 66) $color = 'bg-warning';
                        else $color = 'bg-danger';
                    ?>

                    <div class="row">
                        <div class="col-md-2">
                            <h6><?php echo $item['category']; ?></h6>
                        </div>
                        <div class="col-md-10">
                            <div class="row">
                                <div class="col">
                                    <div class="progress site-primary-radius">
                                        <div class="progress-bar <?php echo $color ?>" role="progressbar" style="width: <?php echo number_format($item['percentage'], 0); ?>%"><?php echo number_format($item['percentage'], 2); ?>%</div>
                                    </div>
                                </div>
                            </div>
                            <div class="row my-3">
                                <div class="col">
                                    <?php foreach($expensesDetails as $details): ?>
                                        <?php if ($details['category_id'] == $item['id']): ?>
                                            <div class="row my-2">
                                                <div class="col-md-3"><strong><?php echo $details['expenses'] ?></strong></div>
                                                <div class="col-md-3"><?php echo date("M d, Y h:ia", strtotime($details['created'])); ?></div>
                                                <div class="col-md-3">₱<?php echo number_format($details['amount'], 2); ?></strong></div>
                                                <div class="col-md-3"><?php echo $details['user'] ?></strong></div>
                                            </div>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </div>
                                
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<script>
    $(function() {
        Swal.fire({
            title: 'How to start',
            text: 'Just go to the budget page by clicking the budget link at the side navigation bar.',
            imageUrl: './../assets/core/images/instruction.png',
            imageWidth: 400,
            imageAlt: 'Custom image',
        });
    })
</script>
<?php require_once '../shared/note.php'; ?>
<?php require_once '../shared/footer.php'; ?>