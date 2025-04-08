<div class="question-visualizer date-response-visualizer">
    <!-- Statistics Summary -->
    <div class="statistics-summary">
        <div class="stat-row">
            <div class="stat-item">
                <div class="stat-label">Total Responses</div>
                <div class="stat-value">{{ isset($data['responses']) ? count($data['responses']) : 0 }}</div>
            </div>
            @if(isset($data['stats']))
                @if(isset($data['stats']['earliest_date']))
                    <div class="stat-item">
                        <div class="stat-label">Earliest Date</div>
                        <div class="stat-value">{{ \Carbon\Carbon::parse($data['stats']['earliest_date'])->format('M d, Y') }}</div>
                    </div>
                @endif
                
                @if(isset($data['stats']['latest_date']))
                    <div class="stat-item">
                        <div class="stat-label">Latest Date</div>
                        <div class="stat-value">{{ \Carbon\Carbon::parse($data['stats']['latest_date'])->format('M d, Y') }}</div>
                    </div>
                @endif
                
                @if(isset($data['stats']['most_common_year']))
                    <div class="stat-item">
                        <div class="stat-label">Most Common Year</div>
                        <div class="stat-value">{{ $data['stats']['most_common_year'] }}</div>
                    </div>
                @endif
            @endif
        </div>
    </div>

    <!-- Chart -->
    @if(isset($chart) && !empty($chart))
        <div class="chart-container">
            <h4>Date Distribution</h4>
            <img src="{{ $chart }}" alt="Date Distribution" class="chart-image">
        </div>
    @endif

    <!-- Date Distribution -->
    @if(isset($data['date_distribution']) && count($data['date_distribution']) > 0)
        <div class="date-distribution">
            <h4>Distribution by Time Period</h4>
            <table>
                <thead>
                    <tr>
                        <th width="40%">Time Period</th>
                        <th width="20%">Count</th>
                        <th width="20%">Percentage</th>
                        <th width="20%">Visualization</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($data['date_distribution'] as $period)
                        <tr>
                            <td>{{ $period['label'] }}</td>
                            <td>{{ $period['count'] }}</td>
                            <td>{{ number_format($period['percentage'], 1) }}%</td>
                            <td>
                                <div class="bar-chart-cell">
                                    <div class="bar" style="width: {{ min($period['percentage'], 100) }}%;"></div>
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
                        <th width="60%">Date</th>
                        <th width="30%">Timestamp</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($data['responses'] as $index => $response)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>
                                @if(isset($response['date']))
                                    {{ \Carbon\Carbon::parse($response['date'])->format('F d, Y') }}
                                @else
                                    <em>No date provided</em>
                                @endif
                            </td>
                            <td>{{ isset($response['created_at']) ? \Carbon\Carbon::parse($response['created_at'])->format('M d, Y H:i') : 'N/A' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div> 