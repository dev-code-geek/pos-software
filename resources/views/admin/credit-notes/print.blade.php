@extends('layouts.admin')

@section('title', 'Credit Note #' . $creditNote->credit_note_number)
@section('page-title', 'Credit Note #' . $creditNote->credit_note_number)

@section('admin-content')
<div class="card">
    <div class="card-header">
        <div class="d-flex justify-content-between">
            <h5>Credit Note #{{ $creditNote->credit_note_number }}</h5>
            <div>
                <button class="btn btn-primary btn-sm" onclick="window.print()"><i class="fas fa-print me-1"></i> Print</button>
                <a href="{{ route('admin.credit-notes.pdf', $creditNote->id) }}" class="btn btn-success btn-sm"><i class="fas fa-file-pdf me-1"></i> Download PDF</a>
                <a href="{{ route('admin.credit-notes.index') }}" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i> Back</a>
            </div>
        </div>
    </div>
    <div class="card-body" id="invoice-content">
        <div class="invoice-box">
            @include('admin.credit-notes.pdf', ['creditNote' => $creditNote])
        </div>
    </div>
</div>

<style>
    @media print {
        .card-header, .card-footer, .btn { display: none; }
        .card { border: none; box-shadow: none; }
        .invoice-box { padding: 0; }
        body { background: white; }
    }
    .invoice-box {
        max-width: 100%;
        background: white;
        padding: 20px;
    }
</style>
@endsection