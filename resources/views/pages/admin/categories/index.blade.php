@extends('layouts.backlayout')

@section('content')
<div class="row">
    <div class="col-md-12 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Category Management</h4>
                
                <!-- Category Add Form -->
                <form action="{{ route('admin.categories.store') }}" method="POST" class="forms-sample mb-4">
                    @csrf
                    <div class="input-group">
                        <input type="text" name="name" class="form-control" placeholder="Enter Category Name" required>
                        <button type="submit" class="btn btn-primary text-white">Add Category</button>
                    </div>
                </form>

                <!-- Category List Table -->
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Category Name</th>
                                <th>Total Prompts</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($categories as $key => $category)
                                <tr>
                                    <td>{{ $key + 1 }}</td>
                                    <td>{{ $category->name }}</td>
                                    <td><span class="badge bg-info">{{ $category->prompts_count ?? 0 }}</span></td>
                                    <td>
                                        <form action="{{ route('admin.categories.destroy', $category->id) }}" method="POST" style="display:inline-block;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm text-white" onclick="return confirm('Are you sure?')">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center">No categories found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection