<div class="question-visualizer likert-scale-visualizer">
    <!-- Statistics Summary -->
    <div class="statistics-summary">
        <div class="stat-row">
            <div class="stat-item">
                <div class="stat-label">Total Responses</div>
                <div class="stat-value">{{ isset($data['responses']) ? count($data['responses']) : 0 }}</div>
            </div>
            @if(isset($data['stats']))
                <div class="stat-item">
                    <div class="stat-label">Average Score</div>
                    <div class="stat-value">{{ number_format($data['stats']['average'] ?? 0, 2) }}</div>
                </div>
                <div class="stat-item">
                    <div class="stat-label">Median</div>
                    <div class="stat-value">{{ $data['stats']['median'] ?? 'N/A' }}</div>
                </div>
                <div class="stat-item">
                    <div class="stat-label">Mode</div>
                    <div class="stat-value">{{ $data['stats']['mode'] ?? 'N/A' }}</div>
                </div>
            @endif
        </div>
    </div>

    <!-- Chart -->
    @if(isset($chart) && !empty($chart))
        <div class="chart-container">
            <h4>Response Distribution</h4>
            <img src="{{ $chart }}" alt="Likert Scale Distribution" class="chart-image">
        </div>
    @endif

    <!-- Scale Options Summary -->
    @if(isset($data['scale_summary']) && count($data['scale_summary']) > 0)
        <div class="scale-summary">
            <h4>Response Distribution</h4>
            <table>
                <thead>
                    <tr>
                        <th width="30%">Scale Option</th>
                        <th width="15%">Value</th>
                        <th width="15%">Count</th>
                        <th width="15%">Percentage</th>
                        <th width="25%">Visualization</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($data['scale_summary'] as $option)
                        <tr>
                            <td>{{ $option['label'] }}</td>
                            <td>{{ $option['value'] }}</td>
                            <td>{{ $option['count'] }}</td>
                            <td>{{ number_format($option['percentage'], 1) }}%</td>
                            <td>
                                <div class="bar-chart-cell">
                                    <div class="bar" style="width: {{ min($option['percentage'], 100) }}%;"></div>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    <!-- Response Details -->
    @if(isset($data['responses']) && count($data['responses']) > 0)
        <div class="responses-table">
            <h4>Individual Responses ({{ count($data['responses']) }})</h4>
            
            <table>
                <thead>
                    <tr>
                        <th width="10%">No.</th>
                        <th width="45%">Selected Option</th>
                        <th width="15%">Value</th>
                        <th width="30%">Timestamp</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($data['responses'] as $index => $response)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>
                                @if(isset($response['option_label']) && !empty($response['option_label']))
                                    {{ $response['option_label'] }}
                                @else
                                    <em>No response provided</em>
                                @endif
                            </td>
                            <td>{{ $response['value'] ?? 'N/A' }}</td>
                            <td>{{ isset($response['created_at']) ? \Carbon\Carbon::parse($response['created_at'])->format('M d, Y H:i') : 'N/A' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div> 