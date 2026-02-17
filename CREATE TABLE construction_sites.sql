CREATE TABLE construction_sites (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    address VARCHAR(255),
    city VARCHAR(100),
    commessa_code VARCHAR(50) UNIQUE, -- Codice univoco cantiere
    status ENUM('aperto', 'chiuso', 'sospeso') DEFAULT 'aperto',
    start_date DATE,
    end_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE pos_documents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    site_id INT, -- FK su construction_sites
    title VARCHAR(255) NOT NULL, -- es: "POS Opere Murarie"
    version INT DEFAULT 1,
    file_path VARCHAR(255), -- Percorso del PDF sul server
    status ENUM('bozza', 'in_approvazione', 'valido', 'scaduto', 'revisionato') DEFAULT 'bozza',
    expiry_date DATE,
    notes TEXT,
    FOREIGN KEY (site_id) REFERENCES construction_sites(id) ON DELETE CASCADE
);

CREATE TABLE pos_resource_assignments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pos_id INT,
    worker_id INT, -- FK sulla tua tabella utenti/lavoratori
    equipment_id INT NULL, -- FK sulla tabella mezzi (opzionale)
    FOREIGN KEY (pos_id) REFERENCES pos_documents(id) ON DELETE CASCADE
);

CREATE TABLE artisan_equipment (
    id INT AUTO_INCREMENT PRIMARY KEY,
    artisan_id INT,
    name VARCHAR(255), -- Es: "Escavatore Bobcat E20"
    plate_number VARCHAR(50), -- Targa o numero telaio
    last_inspection_date DATE, -- Data ultima revisione/verifica INAIL
    insurance_expiry DATE, -- Scadenza assicurazione
    is_safe BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (artisan_id) REFERENCES artisans(id) ON DELETE CASCADE
);


CREATE TABLE artisan_equipment (
    id INT AUTO_INCREMENT PRIMARY KEY,
    artisan_id INT,
    name VARCHAR(255), -- Es: "Escavatore Bobcat E20"
    plate_number VARCHAR(50), -- Targa o numero telaio
    last_inspection_date DATE, -- Data ultima revisione/verifica INAIL
    insurance_expiry DATE, -- Scadenza assicurazione
    is_safe BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (artisan_id) REFERENCES artisans(id) ON DELETE CASCADE
);

ALTER TABLE pos_documents ADD COLUMN rejection_note TEXT NULL AFTER status;