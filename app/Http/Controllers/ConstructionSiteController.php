<?php

public function generateSiteQr($siteId) {
    $site = ConstructionSite::findOrFail($siteId);
    
    // Genera un URL firmato (sicuro) che scade o richiede login
    $url = URL::temporarySignedRoute('admin.safety.site.report', now()->addHours(24), ['site' => $site->id]);

    return QrCode::size(300)->generate($url);
}
