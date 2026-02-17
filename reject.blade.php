<div class="modal fade" id="modal-reject-{{ $pos->id }}">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.pos.reject', $pos->id) }}" method="POST">
                @csrf
                <div class="modal-header bg-danger">
                    <h4 class="modal-title">Motivazione Rifiuto Documento</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <p>Documento: <strong>{{ $pos->title }}</strong></p>
                    <div class="form-group">
                        <label>Nota per l'Artigiano</label>
                        <textarea name="rejection_note" class="form-control" rows="4" required placeholder="Es: Il documento risulta illeggibile o manca la firma dell'RLS..."></textarea>
                    </div>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Annulla</button>
                    <button type="submit" class="btn btn-danger">Invia Rifiuto</button>
                </div>
            </form>
        </div>
    </div>
</div>