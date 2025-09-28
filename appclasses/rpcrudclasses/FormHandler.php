<?php

namespace RpCrudClasses;
// ### Updated:  Fri Sep 19 2025 11:34:14 CDT
// Help file: http://localhost/app5/dashboard/index.php#../app/chatgpt_chats/markdown_files/binding_and_automating_php_queries/bind_pdo_queries_class_part2_php.php

// $host = 'localhost';
// $dbname = 'racepadd_notodb_bs5_2024able_light';
// $username = 'root';
// $password = '';

// try{
// $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
// $pdo = new PDO($dsn, $username, $password);


//     // Set PDO attributes for error handling (optional but recommended)
//     $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
//     $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
//     $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

//     //echo "Database connection successful!";

//     // query to further confirm
//     // $stmt = $pdo->query("SELECT 1");
//     // if ($stmt) {
//     //     echo " - Simple query executed successfully.";
//     // }

// } catch (PDOException $e) {
//     echo "Database connection failed: " . $e->getMessage();
//     // log the error for development
// }

class FormHandler
{
    protected \PDO $pdo;
    protected SqlHelper $sqlHelper;
    protected array $allowedColumns;

    // ~~~~~~ BEGIN Constructor ~~~~~~~
    public function __construct(\PDO $pdo, string $table, array $allowedColumns)
    {
        $this->pdo = $pdo;
        $this->sqlHelper = new SqlHelper($table);
        $this->allowedColumns = $allowedColumns;
    }
    // ~~~~~~ END Constructor ~~~~~~~


    // ~~~~~~ BEGIN Update ~~~~~~~
    public function update(
        array $input,
        array $whereConditions,
        array $rules,
        array $messages = [],
        array $aliases = []
    ) {
        $clean = SqlHelper::filterInput($this->allowedColumns, $input);

        $valid = ValidatorHelper::validate($clean, $rules, $messages, $aliases);
        if ($valid !== true) {
            return $valid;
        }

        $updateSet = SqlHelper::buildUpdateSet(array_keys($clean));
        $whereClause = SqlHelper::buildWhereClause($whereConditions);
        $whereBind = SqlHelper::buildWhereBindArray($whereConditions);

        // $sql = "UPDATE {$this->sqlHelper->table} SET $updateSet WHERE $whereClause";
        $sql = "UPDATE {$this->sqlHelper->getTable()} SET $updateSet WHERE $whereClause";

        $bindArray = array_merge(
            SqlHelper::buildBindArray(array_keys($clean), $clean),
            $whereBind
        );

        $stmt = $this->pdo->prepare($sql);
        if ($stmt->execute($bindArray)) {

        $rowCount = $stmt->rowCount();
        if ($rowCount > 0) {
            return true; // Rows updated
        } else {
            return ['error' => 'No rows matched the update criteria or no changes made.'];
        }


        }

        // return ['database' => 'Update operation failed'];

        $error = $stmt->errorInfo();
        return ['database' => 'Update operation failed', 'errorInfo' => $error];

    }
    // ~~~~~~ END Update ~~~~~~~


    // ~~~~~~ BEGIN Insert ~~~~~~~

    public function insert(
        array $input,
        array $rules,
        array $messages = [],
        array $aliases = []
    ) {
        $clean = SqlHelper::filterInput($this->allowedColumns, $input);

        $valid = ValidatorHelper::validate($clean, $rules, $messages, $aliases);
        if ($valid !== true) {
            return $valid;
        }

        [$cols, $placeholders] = SqlHelper::formatInsertParts(array_keys($clean));
        // $sql = "INSERT INTO {$this->sqlHelper->table} ($cols) VALUES ($placeholders)";
        $sql = "INSERT INTO {$this->sqlHelper->getTable()} ($cols) VALUES ($placeholders)";

        $bindArray = SqlHelper::buildBindArray(array_keys($clean), $clean);

        $stmt = $this->pdo->prepare($sql);

        // return ['database' => 'Insert operation failed'];
        if ($stmt->execute($bindArray)) {

        $rowCount = $stmt->rowCount();
        if ($rowCount > 0) {
            return true; // Row inserted
        } else {
            return ['error' => 'Insert operation did not affect any rows.'];
        }

        }

        $error = $stmt->errorInfo();
        return ['database' => 'Insert operation failed', 'errorInfo' => $error];

    }
    // ~~~~~~ END Insert ~~~~~~~

    // ~~~~~~ BEGIN Delete ~~~~~~~

    // added delete function ### Updated:  Fri Sep 19 2025 11:34:00 CDT

    public function delete(array $whereConditions): bool|array
    {
        if (empty($whereConditions)) {
            return ['error' => 'Delete operation requires at least one WHERE condition to prevent full table delete.'];
        }

        $whereClause = SqlHelper::buildWhereClause($whereConditions);
        $whereBind = SqlHelper::buildWhereBindArray($whereConditions);

        // $sql = "DELETE FROM {$this->sqlHelper->table} WHERE $whereClause";
        $sql = "DELETE FROM {$this->sqlHelper->getTable()} WHERE $whereClause";

        $stmt = $this->pdo->prepare($sql);
        if ($stmt->execute($whereBind)) {

        $rowCount = $stmt->rowCount();
        if ($rowCount > 0) {
            return true; // Rows deleted
        } else {
            return ['error' => 'No rows matched the delete criteria.'];
        }

        }

        // return ['database' => 'Delete operation failed'];
        $error = $stmt->errorInfo();
        return ['database' => 'Delete operation failed', 'errorInfo' => $error];

    }
    // Example Usage for delete:

    /*
    $whereConditions = [
        ['column' => 'id', 'operator' => '=', 'value' => 123, 'boolean' => 'AND'],
        ['column' => 'status', 'operator' => '=', 'value' => 'inactive', 'boolean' => 'AND'],
    ];

    $result = $formHandler->delete($whereConditions);

    if ($result === true) {
        echo "Delete successful";
    } else {
        print_r($result); // error array
    }
    */


    // ~~~~~~ END Delete ~~~~~~~


    // ~~~~~~ BEGIN Select ~~~~~~~
    // added select function ### Updated:  Fri Sep 19 2025 11:34:00 CDT

    public function select(
        array $columns = ['*'],
        array $whereConditions = [],
        ?string $orderBy = null,
        ?int $limit = null,
        ?int $offset = null
    ): array|false {
        // Columns to select
        $cols = implode(', ', $columns);

        // Build WHERE clause if conditions provided
        $whereClause = '';
        $bindArray = [];
        if (!empty($whereConditions)) {
            $whereClause = ' WHERE ' . SqlHelper::buildWhereClause($whereConditions);
            $bindArray = SqlHelper::buildWhereBindArray($whereConditions);
        }

        // Build ORDER BY clause if provided
        $orderClause = $orderBy ? " ORDER BY $orderBy" : '';

        // Build LIMIT and OFFSET clause if provided
        $limitClause = '';
        if ($limit !== null) {
            $limitClause = " LIMIT $limit";
            if ($offset !== null) {
                $limitClause .= " OFFSET $offset";
            }
        }

        // $sql = "SELECT $cols FROM {$this->sqlHelper->table}$whereClause$orderClause$limitClause";
        $sql = "SELECT $cols FROM {$this->sqlHelper->getTable()}$whereClause$orderClause$limitClause";

        $stmt = $this->pdo->prepare($sql);
        if ($stmt->execute($bindArray)) {
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        }

        return false;
    }

    /*
    // Example Usage for Select:

    // Select all active users with role = 'admin', order by created_at desc, limit 10
    $whereConditions = [
        ['column' => 'active', 'operator' => '=', 'value' => 1],
        ['column' => 'role', 'operator' => '=', 'value' => 'admin'],
    ];

    $users = $formHandler->select(
        ['id', 'username', 'email', 'created_at'],
        $whereConditions,
        'created_at DESC',
        10
    );

    print_r($users);

    */

    // ~~~~~~ END Select ~~~~~~~

}