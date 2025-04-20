<?php
namespace Utopia;

use PDO;
use PDOException;
use InvalidArgumentException;

class KoSu
{
    private static PDO $pdo;
    private static string $selectColumns = '*';
    private static array $whereConditions = [];
    private static array $params = [];
    private static string $orderBy = '';
    private static string $limit = '';
    private static array $joins = [];
    private static string $table;
    private static array $setColumns = [];
    private static string $dbType;
    private static array $instances = [];
    private static string $groupBy = '';
    private static string $offset = '';
    private static bool $softDelete = false;
    private static string $deletedAtColumn = 'deleted_at';

    public function __construct(string $host, string $dbname, string $username, string $password, string $dbType = 'mysql')
    {
        try {
            self::$pdo = new PDO("$dbType:host=$host;dbname=$dbname", $username, $password);
            self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            self::$dbType = $dbType;
        } catch (PDOException $e) {
            error_log($e->getMessage());
            throw $e;
        }
    }

    public static function getInstance(string $host, string $dbname, string $username, string $password, string $dbType = 'mysql'): self
    {
        $key = "$host-$dbname";

        if (!isset(self::$instances[$key])) {
            self::$instances[$key] = new self($host, $dbname, $username, $password, $dbType);
        }

        return self::$instances[$key];
    }

    public static function getPDO(): PDO
    {
        return self::$pdo;
    }

    public static function table(string $table): self
    {
        if (empty(self::$instances)) {
            throw new InvalidArgumentException('No instance available. Please initialize an instance first.');
        }
        self::$table = $table;
        return self::$instances[array_key_first(self::$instances)];
    }

    public function select($columns = '*'): self
    {
        self::$selectColumns = is_array($columns) ? implode(', ', $columns) : $columns;
        return $this;
    }

    public function where(string $column, $value, string $operator = '='): self
    {
        self::$whereConditions[] = "$column $operator ?";
        self::$params[] = $value;
        return $this;
    }

    public function orWhere(string $column, $value, string $operator = '='): self
    {
        self::$whereConditions[] = "OR $column $operator ?";
        self::$params[] = $value;
        return $this;
    }

    public function andWhere(string $column, $value, string $operator = '='): self
    {
        self::$whereConditions[] = "AND $column $operator ?";
        self::$params[] = $value;
        return $this;
    }

    public function orderBy(string $column, string $direction = 'ASC'): self
    {
        if (strtoupper($direction) === 'RAND' || strtoupper($direction) === 'RANDOM') {
            self::$orderBy = "ORDER BY RAND()";
        } else {
            self::$orderBy = "ORDER BY $column $direction";
        }
        return $this;
    }

    public function limit(int $limit): self
    {
        self::$limit = "LIMIT $limit";
        return $this;
    }

    public function offset(int $offset): self
    {
        self::$offset = "OFFSET $offset";
        return $this;
    }

    public function join(string $table, string $firstColumn, string $operator, string $secondColumn): self
    {
        self::$joins[] = "JOIN $table ON $firstColumn $operator $secondColumn";
        return $this;
    }

    public function leftJoin(string $table, string $firstColumn, string $operator, string $secondColumn): self
    {
        self::$joins[] = "LEFT JOIN $table ON $firstColumn $operator $secondColumn";
        return $this;
    }

    public function rightJoin(string $table, string $firstColumn, string $operator, string $secondColumn): self
    {
        self::$joins[] = "RIGHT JOIN $table ON $firstColumn $operator $secondColumn";
        return $this;
    }

    public function groupBy($columns): self
    {
        $columns = is_array($columns) ? implode(', ', $columns) : $columns;
        self::$groupBy = "GROUP BY $columns";
        return $this;
    }

    public function enableSoftDeletes(string $deletedAtColumn = 'deleted_at'): self
    {
        self::$softDelete = true;
        self::$deletedAtColumn = $deletedAtColumn;
        return $this;
    }

    public function find(): array
    {
        $sql = "SELECT " . self::$selectColumns . " FROM " . self::$table;

        if (!empty(self::$joins)) {
            $sql .= ' ' . implode(' ', self::$joins);
        }

        if (self::$softDelete) {
            self::$whereConditions[] = self::$deletedAtColumn . " IS NULL";
        }

        if (!empty(self::$whereConditions)) {
            $sql .= " WHERE " . implode(' ', self::$whereConditions);
        }

        $sql .= " " . self::$groupBy . " " . self::$orderBy . " " . self::$limit . " " . self::$offset;

        $stmt = self::$pdo->prepare($sql);
        $stmt->execute(self::$params);
        $this->reset();

        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public function first()
    {
        $this->limit(1);
        $results = $this->find();
        return $results[0] ?? null;
    }

    public function create(array $data): self
    {
        if (empty($data)) {
            throw new InvalidArgumentException('No data provided for create method.');
        }

        $fields = implode(', ', array_keys($data));
        $values = ':' . implode(', :', array_keys($data));

        $sql = "INSERT INTO " . self::$table . " ($fields) VALUES ($values)";
        $stmt = self::$pdo->prepare($sql);
        $stmt->execute($data);
        return $this;
    }

    public function update(array $data): self
    {
        if (empty(self::$whereConditions)) {
            throw new InvalidArgumentException('Update method requires at least one condition.');
        }

        if (empty($data)) {
            throw new InvalidArgumentException('Update method requires at least one column to be set.');
        }

        $setStatements = [];
        foreach ($data as $key => $value) {
            $setStatements[] = "$key = ?";
            self::$params[] = $value;
        }

        $setClause = implode(', ', $setStatements);
        $sql = "UPDATE " . self::$table . " SET $setClause WHERE " . implode(' ', self::$whereConditions);
        $stmt = self::$pdo->prepare($sql);
        $stmt->execute(self::$params);
        $this->reset();

        return $this;
    }

    public function delete(): self
    {
        if (empty(self::$whereConditions)) {
            throw new InvalidArgumentException('Delete method requires at least one condition.');
        }

        if (self::$softDelete) {
            $sql = "UPDATE " . self::$table . " SET " . self::$deletedAtColumn . " = NOW() WHERE " . implode(' ', self::$whereConditions);
        } else {
            $sql = "DELETE FROM " . self::$table . " WHERE " . implode(' ', self::$whereConditions);
        }

        $stmt = self::$pdo->prepare($sql);
        $stmt->execute(self::$params);
        $this->reset();

        return $this;
    }

    public function beginTransaction(): void
    {
        self::$pdo->beginTransaction();
    }

    public function commit(): void
    {
        self::$pdo->commit();
    }

    public function rollBack(): void
    {
        self::$pdo->rollBack();
    }

    public function paginate(int $page = 1, int $perPage = 15): self
    {
        $offset = ($page - 1) * $perPage;
        $this->limit($perPage);
        $this->offset($offset);
        return $this;
    }

    private function reset(): void
    {
        self::$selectColumns = '*';
        self::$whereConditions = [];
        self::$params = [];
        self::$orderBy = '';
        self::$limit = '';
        self::$joins = [];
        self::$groupBy = '';
        self::$offset = '';
    }

    public function fullTextSearch(string $column, string $searchTerm): self
    {
        $column = preg_replace('/[^a-zA-Z0-9_]/', '', $column);
        self::$whereConditions[] = "MATCH($column) AGAINST (? IN NATURAL LANGUAGE MODE)";
        self::$params[] = $searchTerm;
        return $this;
    }

    public function RowCountTable(): int
    {
        $sql = "SELECT COUNT(*) as total FROM " . self::$table;

        if (!empty(self::$whereConditions)) {
            $sql .= " WHERE " . implode(' ', self::$whereConditions);
        }

        $stmt = self::$pdo->prepare($sql);
        $stmt->execute(self::$params);

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $this->reset();
        return $result['total'];
    }

    public function customWhere(string $condition, array $params = []): self
    {
        self::$whereConditions[] = $condition;
        self::$params = array_merge(self::$params, $params);
        return $this;
    }

    public function whereNotEqual(string $column, $value): self
    {
        return $this->where($column, $value, '<>');
    }

    public function like(string $column, string $value): self
    {
        self::$whereConditions[] = "$column LIKE ?";
        self::$params[] = $value;
        return $this;
    }
}
?>