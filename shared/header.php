<?php session_start(); ?>
<?php require_once './../helpers/PageIdex.php'; ?>
<?php require_once './../data/budget/AmountData.php'; ?>
<?php require_once './../repository/budget/AmountRepository.php'; ?>
<?php require_once './../data/budget/ExpensesData.php'; ?>
<?php require_once './../repository/budget/ExpensesRepository.php'; ?>
<?php require_once './../data/budget/SavingsData.php'; ?>
<?php require_once './../repository/budget/SavingsRepository.php'; ?>

<?php $savingsData = new SavingsRepository(); ?>

<?php $id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0; ?>
<?php $account_type = isset($_SESSION['account_type']) ? $_SESSION['account_type'] : 0; ?>
<?php $firstname = isset($_SESSION['firstname']) ? $_SESSION['firstname'] : 0; ?>
<?php $parentId = isset($_SESSION['parent_id']) ? $_SESSION['parent_id'] : 0; ?>
<?php $amount = new AmountRepository(); ?>
<?php $isCycleStarted = $amount->IsCycleStarted($parentId); ?>
<?php $currentCycleId = $amount->GetCurrentCycleId($parentId); ?>
<?php $budget = $amount->GetCurrentCycleBudget($parentId); ?>
<?php $ddate = date('m/d/Y'); ?>
<?php $date  = new DateTime($ddate); ?>
<?php $month = date("F", strtotime($date->format('Y-m-d'))); ?>
<?php $year  = date("Y", strtotime($date->format('Y-m-d'))); ?>
<?php $expenses = new ExpensesRepository(); ?>
<?php $wallet = $expenses->GetRemainingWalletAmount($parentId); ?>
<?php $totalAdjustedSavings = $savingsData->GetTotalAdjustedSavingsByParentId($parentId); ?>
<?php $cycleAdustedSavings = $savingsData->GetAdjustedSavingsByCycleId($currentCycleId); ?>


<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="" />
        <meta name="author" content="" />
        <title>Admin</title>
        <link rel="stylesheet" href="./../../node_modules/font-awesome/css/font-awesome.min.css">
        <link href="./../assets/core/css/admin.css" rel="stylesheet" />
        <link href="https://cdn.datatables.net/1.10.20/css/dataTables.bootstrap4.min.css" rel="stylesheet" crossorigin="anonymous" />
        <link rel="stylesheet" href="./../../node_modules/sweetalert2/dist/sweetalert2.min.css">
        <script src="./../../node_modules/sweetalert2/dist/sweetalert2.all.min.js"></script>
        <script src="https://code.jquery.com/jquery-3.4.1.min.js" crossorigin="anonymous"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.11.2/js/all.min.js" crossorigin="anonymous"></script>
    </head>
    <body class="sb-nav-fixed">
        <nav class="sb-topnav navbar navbar-expand navbar-dark bg-dark" style="background: #376d08 !important;">
            <a class="navbar-brand" href="">
                <img src="./../assets/core/images/pwise-white-logo.png" alt="" style="height: 1.8rem">
            </a>
            <button class="btn btn-link btn-sm order-1 order-lg-0" id="sidebarToggle" href="#"><i class="fas fa-bars"></i></button>
            <!-- Navbar-->
            <ul class="navbar-nav ml-auto">
                <!-- <li class="nav-item dropdown">
                    <a class="nav-link" id="userDropdown" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fa fa-bell"></i></a>
                    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="userDropdown">
                        <p class="text-center">No notifications</p>
                    </div>
                </li> -->
                <li class="nav-item dropdown">
                    <a class="nav-link" id="userDropdown" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fas fa-user fa-fw"></i></a>
                    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="userDropdown">
                        <a class="dropdown-item" href="profile.php">Profile</a>
                        <a class="dropdown-item" href="password.php">Change Password</a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="/auth/logout.php">Logout</a>
                    </div>
                </li>
            </ul>
        </nav>
        <div id="layoutSidenav">
            <div id="layoutSidenav_nav">
                <nav class="sb-sidenav accordion sb-sidenav-dark" id="sidenavAccordion" style="background: #589c1c">
                    <div class="sb-sidenav-menu">
                        <div class="nav">
                            <div class="sb-sidenav-menu-heading text-white">Pages</div>
                            <a class="nav-link text-white" href="dashboard.php">
                                <span>Dashboard</span> 
                            </a>
                            <a class="nav-link text-white" href="budget.php">
                                <span>Budget</span> 
                            </a>
                            <a class="nav-link text-white" href="expenses.php">
                                <span>Expenses</span> 
                            </a>
                            <a class="nav-link text-white" href="savings.php">
                                <span>Savings</span> 
                            </a>

                            <?php if ($account_type == 1): ?>
                                <a class="nav-link text-white" href="members.php">
                                    <span>Household Members</span> 
                                </a>
                            <?php endif; ?>

                            <a class="nav-link text-white" href="reports.php">
                                <span>Reports</span> 
                            </a>
                        </div>
                    </div>
                </nav>
            </div>
            <div id="layoutSidenav_content">
                <main>