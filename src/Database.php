<?php
declare(strict_types=1);

namespace Nefertix;

use PDO;
use PDOStatement;

class Database
{
    public function __construct(
        private PDO $pdo,
    )
    {
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public function prepare(string $sql): PDOStatement
    {
        return $this->pdo->prepare($sql);
    }

    public function query(string $sql): PDOStatement|false
    {
        return $this->pdo->query($sql);
    }

    /**
     * @param string $sql
     * @param array<string|int, mixed>|null $params
     * @return mixed
     */
    public function single(string $sql, ?array $params = null): mixed
    {
        $stmt = $this->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchColumn();
    }

    /**
     * @param string $sql
     * @param array<string|int, mixed>|null $params
     * @return mixed
     */
    public function row(string $sql, ?array $params = null): mixed
    {
        $stmt = $this->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function lastInsertId(?string $sequenceName = null): string|false
    {
        return $this->pdo->lastInsertId($sequenceName);
    }

    public function beginTransaction(): bool
    {
        return $this->pdo->beginTransaction();
    }

    public function commit(): bool
    {
        return $this->pdo->commit();
    }

    public function rollback(): bool
    {
        return $this->pdo->rollBack();
    }

    /**
     * @param string $table
     * @param array<string, mixed> $params
     * @return bool
     */
    public function insert(string $table, array $params): bool
    {
        $columns = implode(',', array_keys($params));
        $placeholders = implode(',', array_map(fn($column) => ":$column", array_keys($params)));

        $sql = "INSERT INTO $table ($columns) VALUES ($placeholders)";

        return $this->prepare($sql)->execute($params);
    }

    /**
     * @param string $table
     * @param array<string|int, mixed> $params
     * @param array<string|int, mixed> $conditions
     * @return bool
     */
    public function update(string $table, array $params, array $conditions): bool
    {
        $set = implode(',', array_map(fn($column) => "$column = ?", array_keys($params)));
        $where = implode(' AND ', array_map(fn($column) => "$column = ?", array_keys($conditions)));

        $sql = "UPDATE $table SET $set WHERE $where";

        return $this->prepare($sql)->execute(array_merge(array_values($params), array_values($conditions)));
    }

    /**
     * @param string $table
     * @param array<string|int, mixed> $conditions
     * @return bool
     */
    public function delete(string $table, array $conditions): bool
    {
        $where = implode(' AND ', array_map(fn($column) => "$column = ?", array_keys($conditions)));

        $sql = "DELETE FROM $table WHERE $where";

        return $this->prepare($sql)->execute(array_merge(array_values($conditions)));
    }
}
