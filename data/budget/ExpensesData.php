<?php

class ExpensesData extends DataHelper {
    public function GetPrioritiesLevel() {
        $sql = "SELECT * FROM priority_level";
        return $this->SelectData($sql);
    }

    public function GetExpensesCategoryByParentId($parentId, $cycleId) {
        $parentId = $this->EscapeString($parentId);
        $cycleId  = $this->EscapeString($cycleId);

        $sql = "SELECT  EC.* 
                        , CONCAT(U.firstname, ' ', U.lastname) as user
                        , IFNULL((
                            SELECT SUM(E.amount)
                                FROM expenses E 
                                INNER JOIN expenses_name EN 
                                    ON E.expenses_name_id = EN.id
                                WHERE EC.id = EN.expenses_category_id
                                    AND E.cycle_id = '$cycleId'
                        ), 0) AS expenses
                        , IFNULL((
                            SELECT BL.amount
                                FROM budget_limit BL
                                WHERE BL.cycle_id = '$cycleId'
                                    AND BL.expensese_category_id = EC.id
                        ), 0) AS limits
                        , IFNULL((
                            SELECT BL.id
                                FROM budget_limit BL
                                WHERE BL.cycle_id = '$cycleId'
                                    AND BL.expensese_category_id = EC.id
                        ), 0) AS limit_id
                    FROM expenses_categories EC
                    INNER JOIN users U
                        ON EC.created_by = U.id
                    WHERE EC.parent_id = '$parentId'";
        return $this->SelectData($sql);
    }

    public function GetExpensesNameByExpensesParentId($parentId) {
        $parentId = $this->EscapeString($parentId);
        
        $sql = "SELECT  EN.* 
                        , CONCAT(U.firstname, ' ', U.lastname) as user
                        , EC.name AS category
                    FROM expenses_name EN
                    INNER JOIN expenses_categories EC 
                        ON EN.expenses_category_id = EC.id
                    INNER JOIN users U
                        ON EN.created_by = U.id
                    WHERE EC.parent_id = '$parentId'";
        return $this->SelectData($sql);
    }

    public function GetMonthlyExpensesByParentId($parentId) {
        $parentId = $this->EscapeString($parentId);
        $ddate = date('m/d/Y');
        $date  = new DateTime($ddate);
        $month = date("m", strtotime($date->format('Y-m-d')));
        $year  = date("Y", strtotime($date->format('Y-m-d')));

        $sql = "SELECT E.*
                       , EN.name AS expenses
                       , EC.name AS category
                       , PL.name AS priority
                       , CONCAT(U.firstname, ' ', U.lastname) AS name
                    FROM expenses E
                    INNER JOIN expenses_name EN ON EN.id = E.expenses_name_id
                    INNER JOIN expenses_categories EC ON EN.expenses_category_id = EC.id
                    INNER JOIN priority_level PL ON PL.id = EC.priority_level_id
                    INNER JOIN users U ON U.id = E.created_by
                    WHERE MONTH(DATE(E.created)) = '$month'
                        AND YEAR(DATE(E.created)) = '$year'
                        AND (
                                U.parent_id = '$parentId'
                                OR U.id = '$parentId'

                            )";
        return $this->SelectData($sql);
    }

    public function GetCategoryByNameAndParentId($parent_id, $newCategory) {
        $parent_id   = $this->EscapeString($parent_id);
        $newCategory = $this->EscapeString($newCategory);

        $sql = "SELECT * 
                    FROM expenses_categories
                    WHERE name = '$newCategory'
                        AND parent_id = '$parent_id'";

        return $this->SelectData($sql);
    }

    public function CreateNewCategory($parent_id, $id, $priority, $newCategory) {
        $parent_id   = $this->EscapeString($parent_id);
        $id          = $this->EscapeString($id);
        $priority    = $this->EscapeString($priority);
        $newCategory = $this->EscapeString($newCategory);

        $sql = "INSERT INTO expenses_categories(name, priority_level_id, parent_id, created_by)
                VALUES ('$newCategory', '$priority', '$parent_id', '$id')";

        return $this->ExecuteNonQueryReturnsInsertedId($sql);
    }

    public function GetExpensesByNameAndParentId($parent_id, $newExpenses) {
        $parent_id   = $this->EscapeString($parent_id);
        $newExpenses = $this->EscapeString($newExpenses);

        $sql = "SELECT * 
                    FROM expenses_name EN
                    INNER JOIN expenses_categories EC ON EC.id = EN.expenses_category_id
                    WHERE EN.name = '$newExpenses'
                        AND EC.parent_id = '$parent_id'";

        return $this->SelectData($sql);
    }

    public function CreateNewExpenses($parent_id, $id, $category, $newExpenses) {
        $parent_id   = $this->EscapeString($parent_id);
        $id          = $this->EscapeString($id);
        $category    = $this->EscapeString($category);
        $newExpenses = $this->EscapeString($newExpenses);

        $sql = "INSERT INTO expenses_name(name, expenses_category_id, created_by)
                VALUES ('$newExpenses', '$category', '$id')";

        return $this->ExecuteNonQueryReturnsInsertedId($sql);
    }

    public function AddExpenses($id, $expenses, $amount, $cycle_id) {
        $id        = $this->EscapeString($id);
        $expenses  = $this->EscapeString($expenses);
        $amount    = $this->EscapeString($amount);
        $cycle_id  = $this->EscapeString($cycle_id);

        $sql = "INSERT INTO expenses(amount, expenses_name_id, created_by, cycle_id)
                VALUES ('$amount', '$expenses', '$id', '$cycle_id')";

        return $this->ExecuteNonQuery($sql);
    }

    public function GetExpensesByParentId($parentId) {
        $parentId = $this->EscapeString($parentId);

        $sql = "SELECT  EC.id
                        , EC.priority_level_id
                        , EC.name AS category
                        , (
                            SELECT SUM(E.amount)
                                FROM expenses E 
                            INNER JOIN expenses_name EN ON E.expenses_name_id = EN.id
                            INNER JOIN cycle C ON C.id = E.cycle_id
                                WHERE (DATE(CURRENT_DATE) BETWEEN DATE(C.start) AND DATE(C.end))
                                    AND EN.expenses_category_id = EC.id
                        ) AS amount
                        , (
                            (
                                (
                                    SELECT SUM(E.amount)
                                        FROM expenses E 
                                        INNER JOIN expenses_name EN ON E.expenses_name_id = EN.id
                                        INNER JOIN cycle C ON C.id = E.cycle_id
                                        WHERE (DATE(CURRENT_DATE()) BETWEEN DATE(C.start) AND DATE(C.end))
                                            AND EN.expenses_category_id = EC.id
                                ) / (
                                        SELECT SUM(amount)
                                            FROM budgets B
                                            INNER JOIN users U ON U.id = B.created_by
                                            INNER JOIN cycle C ON C.id = B.cycle_id
                                            WHERE (DATE(CURRENT_DATE()) BETWEEN DATE(C.start) AND DATE(C.end))
                                                AND (U.id = '$parentId' OR U.parent_id = '$parentId')
                                )
                            ) * 100
                        ) AS percentage
                    FROM expenses_categories EC
                    WHERE EC.parent_id = '$parentId'";

        return $this->SelectData($sql);
    }

    public function GetExpensesDetailsByParentId($parentId) {
        $parentId = $this->EscapeString($parentId);

        $sql = "SELECT  E.id
                        , E.amount
                        , E.created
                        , EN.name AS expenses 
                        , EC.name AS category 
                        , PL.name AS priority
                        , CONCAT(U.firstname, ' ', U.lastname) AS user
                        , EC.id AS category_id
                    FROM expenses E 
                    INNER JOIN expenses_name EN ON EN.id = E.expenses_name_id
                    INNER JOIN expenses_categories EC ON EC.id = EN.expenses_category_id
                    INNER JOIN priority_level PL ON PL.id = EC.priority_level_id
                    INNER JOIN users U ON E.created_by = U.id
                    INNER JOIN cycle C ON C.id = E.cycle_id
                    WHERE (U.id = '$parentId' OR U.parent_id = '$parentId')
                        AND (DATE(CURRENT_DATE()) BETWEEN DATE(C.start) AND DATE(C.end))
                    ORDER BY E.id DESC;";

        return $this->SelectData($sql);
    }

    public function GetRemainingWalletAmount($parentId) {
        $parentId = $this->EscapeString($parentId);
        $sql = "SELECT (
                    IFNULL((
                        SELECT SUM(B.amount)
                            FROM budgets B 
                            INNER JOIN users U ON U.id = B.created_by
                            INNER JOIN cycle C ON C.id = B.cycle_id
                            WHERE (U.parent_id = '$parentId' OR U.id = '$parentId')
                                AND DATE(CURRENT_DATE()) BETWEEN DATE(C.start) AND DATE(C.end)
                    ), 0.00) - IFNULL((
                        SELECT SUM(E.amount)
                        FROM expenses E 
                        INNER JOIN expenses_name EN ON EN.id = E.expenses_name_id
                        INNER JOIN expenses_categories EC ON EC.id = EN.expenses_category_id
                        INNER JOIN cycle C ON C.id = E.cycle_id
                        WHERE EC.parent_id = '$parentId'
                            AND DATE(CURRENT_DATE()) BETWEEN DATE(C.start) AND DATE(C.end)
                    ), 0.00)
                ) AS wallet;";

        return $this->SelectData($sql);
    }

    public function GetRecentSavingsAmount($parentId, $month, $year) {
        $parentId = $this->EscapeString($parentId);
        $sql = "SELECT id
                       , (
                            IFNULL((
                                SELECT SUM(B.amount)
                                    FROM budgets B 
                                    INNER JOIN users U ON U.id = B.created_by
                                    WHERE (U.parent_id = '2' OR U.id = '2')
                                        AND B.cycle_id = C.id
                                        
                            ), 0.00) - IFNULL((
                                SELECT SUM(E.amount)
                                FROM expenses E 
                                INNER JOIN expenses_name EN ON EN.id = E.expenses_name_id
                                INNER JOIN expenses_categories EC ON EC.id = EN.expenses_category_id
                                WHERE EC.parent_id = '2'
                                    AND E.cycle_id = C.id
                            ), 0.00)
                       ) AS savings
                    FROM cycle C
                    WHERE NOT (DATE(CURRENT_DATE()) BETWEEN DATE(C.start) AND DATE(C.end))
          	        ORDER BY C.id DESC
          	        LIMIT 1;";

        return $this->SelectData($sql);
    }

    public function GetTotalSavingsAmount($parentId) {
        $parentId = $this->EscapeString($parentId);
        $sql = "SELECT (
                    (
                        (
                            IFNULL((
                                SELECT SUM(B.amount)
                                    FROM budgets B 
                                    INNER JOIN users U ON U.id = B.created_by
                                    INNER JOIN cycle C ON B.cycle_id = C.id
                                    WHERE (U.parent_id = '$parentId' OR U.id = '$parentId')
                                        AND NOT (DATE(CURRENT_DATE()) BETWEEN DATE(C.start) AND DATE(C.end))
                            ), 0.00) - IFNULL((
                                SELECT SUM(E.amount)
                                    FROM expenses E 
                                    INNER JOIN expenses_name EN ON EN.id = E.expenses_name_id
                                    INNER JOIN expenses_categories EC ON EC.id = EN.expenses_category_id
                                    INNER JOIN cycle C ON C.id = E.cycle_id
                                    WHERE EC.parent_id = '$parentId'
                                        AND NOT (DATE(CURRENT_DATE()) BETWEEN DATE(C.start) AND DATE(C.end))
                            ), 0.00)
                        ) + IFNULL(
                            (
                                SELECT SUM(amount)
                                    FROM savings
                                        WHERE action = 'ADD'
                            ), 0.00)
                    ) - IFNULL((SELECT SUM(amount)
                                FROM savings
                                    WHERE action = 'ADJUST')
                    , 0.00)
                ) AS savings";

        return $this->SelectData($sql);
    }
}

?>