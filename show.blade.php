@extends('layouts.admin')

@section('content_header')
    <div class="d-flex justify-content-between">
        <h1>Fascicolo Commessa: {{ $commessa->name }}</h1>
        <a href="{{ route('admin.commesse.index') }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Torna al Quadro Comandi
        </a>
    </div>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-4">
            <div class="card card-primary card-outline">
                <div class="card-body box-profile">
                    <div class="text-center">
                        {!! $commessa->getGlobalSafetyBadge() !!}
                    </div>
                    <h3 class="profile-username text-center mt-3">{{ $commessa->commessa_code }}</h3>
                    <p class="text-muted text-center">{{ $commessa->address }}, {{ $commessa->city }}</p>

                    <ul class="list-group list-group-unbordered mb-3">
                        <li class="list-group-item">
                            <b>Data Inizio</b> <a class="float-right">{{ $commessa->start_date->format('d/m/Y') }}</a>
                        </li>
                        <li class="list-group-item">
                            <b>Ditte Assegnate</b> <a class="float-right">{{ $commessa->posDocuments->unique('artisan_id')->count() }}</a>
                        </li>
                        <li class="list-group-item">
                            <b>Operai Totali</b> <a class="float-right">{{ $commessa->posDocuments->flatMap->workers->unique('id')->count() }}</a>
                        </li>
                    </ul>
                    
                    <button class="btn btn-primary btn-block"><b>Scarica Fascicolo PDF</b></button>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card">
                <div class="card-header p-2">
                    <ul class="nav nav-pills">
                        <li class="nav-item"><a class="nav-link active" href="#pos" data-toggle="tab">Documentazione POS</a></li>
                        <li class="nav-item"><a class="nav-link" href="#mezzi" data-toggle="tab">Mezzi & Attrezzature</a></li>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content">
                        <div class="active tab-pane" id="pos">
                            @forelse($commessa->posDocuments as $pos)
                                <div class="post mb-4 border-bottom pb-3">
                                    <div class="user-block">
                                        <span class="username">
                                            <a href="#">{{ $pos->artisan->company_name }}</a>
                                            <span class="float-right">
                                                @if($pos->status == 'valido')
                                                    <span class="badge badge-success">Approvato</span>
                                                @elseif($pos->status == 'in_approvazione')
                                                    <span class="badge badge-warning text-dark">In Attesa</span>
                                                @else
                                                    <span class="badge badge-danger">Revisionato</span>
                                                @endif
                                            </span>
                                        </span>
                                        <span class="description">Caricato il: {{ $pos->created_at ? $pos->created_at->format('d/m/Y H:i') : 'Data non disponibile' }}</span>
                                    </div>
                                    
                                    <div class="row mt-3">
                                        <div class="col-sm-6">
                                            <p><strong>Documento:</strong> {{ $pos->title }} (Scadenza: {{ $pos->expiry_date->format('d/m/Y') }})</p>
                                            <a href="{{ asset('storage/'.$pos->file_path) }}" target="_blank" class="btn btn-sm btn-outline-info">
                                                <i class="fas fa-file-pdf"></i> Visualizza POS
                                            </a>
                                        </div>
                                        <div class="col-sm-6 text-right">
                                            @if($pos->status == 'in_approvazione')
                                                <form action="{{ route('admin.pos.approve', $pos->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button class="btn btn-success btn-sm">Approva</button>
                                                </form>
                                                <button class="btn btn-danger btn-sm" data-toggle="modal" data-target="#rejectModal{{$pos->id}}">Richiedi Modifiche</button>
                                            @endif
                                        </div>
                                    </div>

                                    @if($pos->rejection_note)
                                        <div class="alert alert-light mt-2 border">
                                            <small><strong>Ultima Nota di Rifiuto:</strong> {{ $pos->rejection_note }}</small>
                                        </div>
                                    @endif
                                </div>
                                
                                {{-- Includiamo il Modal di Rifiuto che avevamo preparato --}}
                                @include('admin.safety.modals.reject', ['pos' => $pos])

                            @empty
                                <div class="text-center py-5">
                                    <i class="fas fa-folder-open fa-3x text-muted"></i>
                                    <p class="mt-2">Nessun documento caricato per questa commessa.</p>
                                </div>
                            @endforelse
                        </div>

                        <div class="tab-pane" id="mezzi">
                            {{-- Qui andrà la lista dei mezzi/attrezzature aggregate --}}
                            <p class="text-muted">Elenco dei mezzi autorizzati per il cantiere...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection