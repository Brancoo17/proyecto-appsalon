<?php

namespace Model;

class HorarioBloqueado extends ActiveRecord {
    protected static string $tabla = 'horarios_bloqueados';
    protected static array $columnasDB = ['id', 'peluquero_id', 'fecha', 'hora_inicio', 'hora_fin', 'motivo'];

    public ?int $id;
    public ?int $peluquero_id;
    public ?string $fecha;
    public ?string $hora_inicio;
    public ?string $hora_fin;
    public ?string $motivo;

    // Propiedades adicionales para joins en vistas
    public ?string $peluquero = null;

    public function __construct($args = []) {
        $this->id = $args['id'] ?? null;
        $this->peluquero_id = isset($args['peluquero_id']) && $args['peluquero_id'] !== '' ? (int)$args['peluquero_id'] : null;
        $this->fecha = $args['fecha'] ?? '';
        $this->hora_inicio = $args['hora_inicio'] ?? '';
        $this->hora_fin = $args['hora_fin'] ?? '';
        $this->motivo = !empty($args['motivo']) ? trim($args['motivo']) : null;
    }

    public static function crearTablaSiNoExiste(): void {
        $db = self::getDB();
        $query = "CREATE TABLE IF NOT EXISTS `horarios_bloqueados` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `peluquero_id` INT NULL DEFAULT NULL,
            `fecha` DATE NOT NULL,
            `hora_inicio` TIME NOT NULL,
            `hora_fin` TIME NOT NULL,
            `motivo` VARCHAR(150) NULL DEFAULT NULL,
            INDEX `idx_fecha` (`fecha`),
            INDEX `idx_peluquero` (`peluquero_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
        $db->query($query);
    }
}
