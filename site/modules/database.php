<?php
class Database {
    private $db;

    public function __construct($path) {
        try {
            $this->db = new PDO("sqlite:" . $path);
            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("Connection failed: " . $e->getMessage());
        }
    }

    public function Execute($sql) {
        try {
            $this->db->exec($sql);
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }

    public function Fetch($sql) {
        try {
            $stmt = $this->db->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    public function Create($table, $data) {
        $columns = implode(", ", array_keys($data));
        $values = ":" . implode(", :", array_keys($data));
        $sql = "INSERT INTO $table ($columns) VALUES ($values)";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($data);
            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            return false;
        }
    }

    public function Read($table, $id) {
        $sql = "SELECT * FROM $table WHERE id = :id";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['id' => $id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function Update($table, $id, $data) {
        $set = [];
        foreach ($data as $key => $value) {
            $set[] = "$key = :$key";
        }
        $set = implode(", ", $set);
        $sql = "UPDATE $table SET $set WHERE id = :id";
        try {
            $data['id'] = $id;
            $stmt = $this->db->prepare($sql);
            return $stmt->execute($data);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function Delete($table, $id) {
        $sql = "DELETE FROM $table WHERE id = :id";
        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute(['id' => $id]);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function Count($table) {
        $sql = "SELECT COUNT(*) as count FROM $table";
        try {
            $stmt = $this->db->query($sql);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['count'];
        } catch (PDOException $e) {
            return 0;
        }
    }
}