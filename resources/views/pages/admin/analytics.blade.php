@extends('layouts.backlayout')

@section('content')
<div class="content-wrapper">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="page-title font-weight-bold text-dark mb-1">Prompt Copies Analytics</h3>
            <p class="text-muted mb-0">Date-wise activity breakdown of prompt copies</p>
        </div>
        <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary btn-sm">&larr; Back to Dashboard</a>
    </div>

    <div class="row">
        <div class="col-12 grid-margin stretch-card">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body">
                    <h4 class="card-title">Daily Copy Activity</h4>
                    <canvas id="copiesChart" style="max-height: 400px;"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        const ctx = document.getElementById('copiesChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: {!! json_encode($dates) !!},
                datasets: [{
                    label: 'Copies per Day',
                    data: {!! json_encode($counts) !!},
                    borderColor: '#0D6EFD',
                    backgroundColor: 'rgba(13, 110, 253, 0.1)',
                    fill: true,
                    tension: 0.3,
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: { 
                        beginAtZero: true, 
                        suggestedMax: 5,
                        ticks: { precision: 0 } 
                    }
                }
            }
        });
    });
</script>
@endsection