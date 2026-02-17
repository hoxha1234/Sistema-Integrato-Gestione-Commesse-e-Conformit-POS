<?php

namespace App\Services;

use App\Models\ArtisanWorker;
use App\Models\ArtisanEquipment;

class PosValidationService
{
    /**
     * Valida la squadra e i mezzi prima di permettere il caricamento del POS
     */
    public function checkCompliance($workerIds, $equipmentIds)
    {
        $errors = [];

        // 1. Controllo Lavoratori
        $workers = ArtisanWorker::whereIn('id', $workerIds)->get();
        foreach ($workers as $worker) {
            if ($worker->medical_check_expiry < now()) {
                $errors[] = "Lavoratore {$worker->first_name} {$worker->last_name}: Visita medica scaduta.";
            }
            if ($worker->safety_training_expiry < now()) {
                $errors[] = "Lavoratore {$worker->first_name} {$worker->last_name}: Corso sicurezza scaduto.";
            }
        }

        // 2. Controllo Mezzi
        $equipments = ArtisanEquipment::whereIn('id', $equipmentIds)->get();
        foreach ($equipments as $item) {
            if ($item->insurance_expiry < now()) {
                $errors[] = "Mezzo {$item->name}: Assicurazione scaduta.";
            }
        }

        return [
            'is_valid' => count($errors) === 0,
            'errors' => $errors
        ];
    }
}