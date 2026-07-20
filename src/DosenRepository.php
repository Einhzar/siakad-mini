<?php
require_once __DIR__ . '/../config/database.php';

class DosenRepository
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getInstance();
    }

    public function all(string $search = '', int $limit = 5, int $offset = 0): array
    {
        $sql = "SELECT * FROM dosen WHERE deleted_at IS NULL";
        $params = [];

        if ($search !== '') {
            $sql .= ' AND (nama LIKE :search OR nidn LIKE :search)';
            $params[':search'] = '%' . $search . '%';
        }

        $sql .= ' ORDER BY created_at DESC LIMIT :limit OFFSET :offset';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

        if ($search !== '') {
            $stmt->bindValue(':search', $params[':search']);
        }

        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function listWithFilters(string $search = '', array $filters = [], string $sort = 'created_at', string $order = 'DESC', int $limit = 5, int $offset = 0): array
    {
        $allowedSorts = ['nidn', 'nama', 'program_studi', 'status', 'created_at'];
        $sort = in_array($sort, $allowedSorts, true) ? $sort : 'created_at';
        $order = strtoupper($order) === 'ASC' ? 'ASC' : 'DESC';

        $sql = 'SELECT d.id, d.nidn, d.nama, d.email, d.program_studi, d.status, d.created_at, d.updated_at, d.deleted_at, COUNT(dm.matakuliah_id) AS matakuliah_count FROM dosen d LEFT JOIN dosen_matakuliah dm ON dm.dosen_id = d.id WHERE d.deleted_at IS NULL';
        $params = [];

        if ($search !== '') {
            $sql .= ' AND (d.nama LIKE :search OR d.nidn LIKE :search)';
            $params[':search'] = '%' . $search . '%';
        }

        if (!empty($filters['program_studi'])) {
            $sql .= ' AND d.program_studi = :program_studi';
            $params[':program_studi'] = $filters['program_studi'];
        }

        if (!empty($filters['status'])) {
            $sql .= ' AND d.status = :status';
            $params[':status'] = $filters['status'];
        }

        $sql .= ' GROUP BY d.id, d.nidn, d.nama, d.email, d.program_studi, d.status, d.created_at, d.updated_at, d.deleted_at ORDER BY d.' . $sort . ' ' . $order . ' LIMIT :limit OFFSET :offset';

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

        if ($search !== '') {
            $stmt->bindValue(':search', $params[':search']);
        }

        if (!empty($filters['program_studi'])) {
            $stmt->bindValue(':program_studi', $params[':program_studi']);
        }

        if (!empty($filters['status'])) {
            $stmt->bindValue(':status', $params[':status']);
        }

        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function count(string $search = ''): int
    {
        $sql = 'SELECT COUNT(*) FROM dosen WHERE deleted_at IS NULL';
        $params = [];

        if ($search !== '') {
            $sql .= ' AND (nama LIKE :search OR nidn LIKE :search)';
            $params[':search'] = '%' . $search . '%';
        }

        $stmt = $this->pdo->prepare($sql);
        if ($search !== '') {
            $stmt->bindValue(':search', $params[':search']);
        }

        $stmt->execute();
        return (int) $stmt->fetchColumn();
    }

    public function countWithFilters(string $search = '', array $filters = []): int
    {
        $sql = 'SELECT COUNT(*) FROM dosen d WHERE d.deleted_at IS NULL';
        $params = [];

        if ($search !== '') {
            $sql .= ' AND (d.nama LIKE :search OR d.nidn LIKE :search)';
            $params[':search'] = '%' . $search . '%';
        }

        if (!empty($filters['program_studi'])) {
            $sql .= ' AND d.program_studi = :program_studi';
            $params[':program_studi'] = $filters['program_studi'];
        }

        if (!empty($filters['status'])) {
            $sql .= ' AND d.status = :status';
            $params[':status'] = $filters['status'];
        }

        $stmt = $this->pdo->prepare($sql);

        if ($search !== '') {
            $stmt->bindValue(':search', $params[':search']);
        }

        if (!empty($filters['program_studi'])) {
            $stmt->bindValue(':program_studi', $params[':program_studi']);
        }

        if (!empty($filters['status'])) {
            $stmt->bindValue(':status', $params[':status']);
        }

        $stmt->execute();
        return (int) $stmt->fetchColumn();
    }

    public function create(array $data): int
    {
        $stmt = $this->pdo->prepare('INSERT INTO dosen (nidn, nama, email, program_studi, foto, status) VALUES (:nidn, :nama, :email, :program_studi, :foto, :status)');
        $stmt->execute($data);
        return (int) $this->pdo->lastInsertId();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM dosen WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $data = $stmt->fetch();
        return $data ?: null;
    }

    public function update(int $id, array $data): void
    {
        $stmt = $this->pdo->prepare('UPDATE dosen SET nidn = :nidn, nama = :nama, email = :email, program_studi = :program_studi, foto = :foto, status = :status WHERE id = :id');
        $data[':id'] = $id;
        $stmt->execute($data);
    }

    public function softDelete(int $id): void
    {
        $stmt = $this->pdo->prepare('UPDATE dosen SET deleted_at = NOW() WHERE id = :id');
        $stmt->execute([':id' => $id]);
    }

    public function restore(int $id): void
    {
        $stmt = $this->pdo->prepare('UPDATE dosen SET deleted_at = NULL WHERE id = :id');
        $stmt->execute([':id' => $id]);
    }

    public function trash(): array
    {
        $stmt = $this->pdo->query('SELECT * FROM dosen WHERE deleted_at IS NOT NULL ORDER BY deleted_at DESC');
        return $stmt->fetchAll();
    }

    public function setMatkul(int $dosenId, array $matkulIds): void
    {
        $this->pdo->beginTransaction();
        try {
            $this->pdo->prepare('DELETE FROM dosen_matakuliah WHERE dosen_id = :dosen_id')->execute([':dosen_id' => $dosenId]);
            if ($matkulIds) {
                $sql = 'INSERT INTO dosen_matakuliah (dosen_id, matakuliah_id, semester) VALUES ';
                $parts = [];
                $params = [];
                foreach ($matkulIds as $index => $matkulId) {
                    $parts[] = '(:dosen_id' . $index . ', :matakuliah_id' . $index . ', :semester' . $index . ')';
                    $params[':dosen_id' . $index] = $dosenId;
                    $params[':matakuliah_id' . $index] = $matkulId;
                    $params[':semester' . $index] = 'Ganjil';
                }
                $stmt = $this->pdo->prepare($sql . implode(', ', $parts));
                $stmt->execute($params);
            }
            $this->pdo->commit();
        } catch (Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function getMatkulForDosen(int $dosenId): array
    {
        $stmt = $this->pdo->prepare('SELECT matakuliah_id FROM dosen_matakuliah WHERE dosen_id = :dosen_id');
        $stmt->execute([':dosen_id' => $dosenId]);
        return array_column($stmt->fetchAll(), 'matakuliah_id');
    }

    public function getAllMatkul(): array
    {
        $stmt = $this->pdo->query('SELECT * FROM mata_kuliah ORDER BY nama');
        return $stmt->fetchAll();
    }
}
