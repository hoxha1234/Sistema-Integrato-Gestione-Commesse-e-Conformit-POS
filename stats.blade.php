<div class="row">
    <div class="col-12 col-sm-6 col-md-3">
        <div class="info-box shadow-sm">
            <span class="info-box-icon bg-warning"><i class="fas fa-file-signature"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Da Revisionare</span>
                <span class="info-box-number text-lg">{{ $stats['pending'] ?? 0 }}</span>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-md-3">
        <div class="info-box shadow-sm">
            <span class="info-box-icon bg-success"><i class="fas fa-check-double"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Documenti Validi</span>
                <span class="info-box-number text-lg">{{ $stats['valid'] ?? 0 }}</span>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-md-3">
        <div class="info-box shadow-sm">
            <span class="info-box-icon bg-danger"><i class="fas fa-clock"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">POS Scaduti</span>
                <span class="info-box-number text-lg">{{ $stats['expired'] ?? 0 }}</span>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-md-3">
        <div class="info-box shadow-sm">
            <span class="info-box-icon bg-info"><i class="fas fa-map-marked-alt"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Cantieri Attivi</span>
                <span class="info-box-number text-lg">{{ $stats['total_sites'] ?? 0 }}</span>
            </div>
        </div>
    </div>
</div>