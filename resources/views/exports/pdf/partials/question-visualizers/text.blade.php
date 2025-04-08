<div class="question-visualizer text-visualizer">
    <!-- Statistics Summary -->
    <div class="statistics-summary">
        <div class="stat-row">
            <div class="stat-item">
                <div class="stat-label">Total Responses</div>
                <div class="stat-value">{{ isset($data['responses']) ? count($data['responses']) : 0 }}</div>
            </div>
            <div class="stat-item">
                <div class="stat-label">Average Length</div>
                <div class="stat-value">{{ isset($data['average_length']) ? number_format($data['average_length'], 1) : 'N/A' }} chars</div>
            </div>
        </div>
    </div>

    <!-- Word Cloud -->
    @if(isset($chart) && !empty($chart))
        <div class="chart-container">
            <h4>Common Words</h4>
            <img src="{{ $chart }}" alt="Word Cloud" class="chart-image">
        </div>
    @endif

    <!-- Response Details -->
    @if(isset($data['responses']) && count($data['responses']) > 0)
        <div class="responses-table">
            <h4>Individual Responses ({{ count($data['responses']) }})</h4>
            
            <table>
                <thead>
                    <tr>
                        <th width="15%">No.</th>
                        <th width="55%">Response</th>
                        <th width="30%">Timestamp</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($data['responses'] as $index => $response)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $response['value'] ?? 'N/A' }}</td>
                            <td>{{ isset($response['created_at']) ? \Carbon\Carbon::parse($response['created_at'])->format('M d, Y H:i') : 'N/A' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div> 