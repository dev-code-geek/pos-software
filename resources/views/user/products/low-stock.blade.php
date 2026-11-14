@extends('admin.layouts.app')

@section('title', 'Low Stock Products')
@section('page-title', 'Low Stock Products')
@section('page-subtitle', 'Products that need restocking')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Products Below Reorder Level</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.products.index') }}" class="btn btn-sm btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Products
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if($lowStockProducts->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Product Name</th>
                                        <th>Category</th>
                                        <th>Supplier</th>
                                        <th>Current Stock</th>
                                        <th>Reorder Level</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($lowStockProducts as $index => $product)
                                        <tr>
                                            <td>{{ $loop->iteration + ($lowStockProducts->currentPage() - 1) * $lowStockProducts->perPage() }}</td>
                                            <td>{{ $product->name }}</td>
                                            <td>{{ $product->category->name ?? 'N/A' }}</td>
                                            <td>{{ $product->supplier->name ?? 'N/A' }}</td>
                                            <td>
                                                <span class="badge bg-danger">{{ $product->quantity }}</span>
                                            </td>
                                            <td>{{ $product->reorder_level ?? 0 }}</td>
                                            <td>
                                                @if($product->quantity <= 0)
                                                    <span class="badge bg-danger">Out of Stock</span>
                                                @elseif($product->quantity <= $product->reorder_level)
                                                    <span class="badge bg-warning text-dark">Low Stock</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    @can('product.view')
                                                        <a href="{{ route('admin.products.show', $product->id) }}" class="btn btn-info" title="View">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                    @endcan
                                                    @can('product.edit')
                                                        <a href="{{ route('admin.products.edit', $product->id) }}" class="btn btn-primary" title="Edit">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                    @endcan
                                                    @can('product.create')
                                                        <a href="{{ route('admin.products.create') }}?copy={{ $product->id }}" class="btn btn-secondary" title="Duplicate">
                                                            <i class="fas fa-copy"></i>
                                                        </a>
                                                    @endcan
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="d-flex justify-content-center mt-3">
                            {{ $lowStockProducts->links() }}
                        </div>
                    @else
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i> All products are adequately stocked. No low stock items found.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .table th {
        white-space: nowrap;
    }
    .badge {
        font-size: 0.9rem;
    }
</style>
@endpush