@extends('layouts.admin')

@section('content_header')
    <h1><i class="fas fa-hard-hat mr-2"></i> Quadro Comandi Commesse</h1>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        {{-- Loop attraverso gli stati della Commessa --}}
        @foreach(['apertura', 'verifica_tecnica', 'in_corso', 'completato'] as $stato)
            <div class="col-md-3">
                <div class="card card-outline card-{{ $stato == 'in_corso' ? 'success' : 'secondary' }}">
                    <div class="card-header">
                        <h3 class="card-title text-uppercase font-weight-bold">
                            {{ str_replace('_', ' ', $stato) }}
                        </h3>
                    </div>
                    <div class="card-body p-2 bg-light" style="min-height: 70vh;">
                        
                        @forelse($commesse->get($stato, []) as $commessa)
                            <div class="card shadow-sm mb-3 border-left-{{ 
                                $commessa->getGlobalSafetyStatus() == 'success' ? 'success' : 
                                ($commessa->getGlobalSafetyStatus() == 'warning' ? 'warning' : 'danger') 
                            }}">
                                <div class="card-body p-3">
                                    <div class="d-flex justify-content-between">
                                        <small class="text-muted">#{{ $commessa->id }}</small>
                                        {!! $commessa->getGlobalSafetyBadge() !!}
                                    </div>
                                    
                                    <h5 class="mt-2 font-weight-bold">
                                        <a href="{{ route('admin.commesse.show', $commessa->id) }}" class="text-dark">
                                            {{ $commessa->name }}
                                        </a>
                                    </h5>
                                    
                                    <div class="small text-muted mb-3">
                                        <i class="fas fa-map-marker-alt"></i> {{ $commessa->city }}
                                    </div>

                                    {{-- Info Rapide POS --}}
                                    <div class="bg-white p-2 rounded border">
                                        @if($commessa->getGlobalSafetyStatus() == 'danger')
                                            <div class="mt-2">
                                                <small class="text-danger font-weight-bold">Ditte non in regola:</small><br>
                                                @foreach($commessa->getIrregularArtisans() as $ditta)
                                                    <span class="badge badge-danger" style="font-size: 0.7rem;">{{ $ditta }}</span>
                                                @endforeach
                                            </div>
                                        @endif
                                        <div class="d-flex justify-content-between small">
                                            <span>POS Totali:</span>
                                            <span class="font-weight-bold">{{ $commessa->pos_documents_count ?? $commessa->posDocuments->count() }}</span>
                                        </div>
                                        @if($commessa->pos_pending > 0)
                                            <div class="text-danger small font-weight-bold mt-1">
                                                <i class="fas fa-clock"></i> {{ $commessa->pos_pending }} da revisionare
                                            </div>
                                        @endif
                                    </div>

                                    <div class="mt-3">
                                        <a href="{{ route('admin.commesse.show', $commessa->id) }}" class="btn btn-xs btn-block btn-outline-primary">
                                            Gestisci Fascicolo
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <p class="text-center text-muted small mt-4">Nessuna commessa in questa fase</p>
                        @endforelse

                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection
