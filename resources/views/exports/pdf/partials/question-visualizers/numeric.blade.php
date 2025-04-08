<div class="question-visualizer numeric-visualizer">
    <!-- Statistics Summary -->
    <div class="statistics-summary">
        <div class="stat-row">
            <div class="stat-item">
                <div class="stat-label">Total Responses</div>
                <div class="stat-value">{{ isset($data['responses']) ? count($data['responses']) : 0 }}</div>
            </div>
            
            @if(isset($data['stats']))
                @if(isset($data['stats']['average']))
                    <div class="stat-item">
                        <div class="stat-label">Average</div>
                        <div class="stat-value">{{ number_format($data['stats']['average'], 2) }}</div>
                    </div>
                @endif
                
                @if(isset($data['stats']['median']))
                    <div class="stat-item">
                        <div class="stat-label">Median</div>
                        <div class="stat-value">{{ number_format($data['stats']['median'], 2) }}</div>
                    </div>
                @endif
        </div>
        
        <div class="stat-row">
                @if(isset($data['stats']['min']))
                    <div class="stat-item">
                        <div class="stat-label">Minimum</div>
                        <div class="stat-value">{{ number_format($data['stats']['min'], 2) }}</div>
                    </div>
                @endif
                
                @if(isset($data['stats']['max']))
                    <div class="stat-item">
                        <div class="stat-label">Maximum</div>
                        <div class="stat-value">{{ number_format($data['stats']['max'], 2) }}</div>
                    </div>
                @endif
                
                @if(isset($data['stats']['std_dev']))
                    <div class="stat-item">
                        <div class="stat-label">Std. Deviation</div>
                        <div class="stat-value">{{ number_format($data['stats']['std_dev'], 2) }}</div>
                    </div>
                @endif
            @endif
        </div>
    </div>

    <!-- Distribution Chart -->
    @if(isset($chart) && !empty($chart))
        <div class="chart-container">
            <h4>Value Distribution</h4>
            <img src="{{ $chart }}" alt="Numeric Value Distribution" class="chart-image">
        </div>
    @endif

    <!-- Distribution Table (if available) -->
    @if(isset($data['distribution']) && count($data['distribution']) > 0)
        <div class="distribution-table">
            <h4>Value Distribution</h4>
            <table>
                <thead>
                    <tr>
                        <th width="40%">Range</th>
                        <th width="20%">Count</th>
                        <th width="20%">Percentage</th>
                        <th width="20%">Visualization</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($data['distribution'] as $range)
                        <tr>
                            <td>{{ $range['label'] }}</td>
                            <td>{{ $range['count'] }}</td>
                            <td>{{ number_format($range['percentage'], 1) }}%</td>
                            <td>
                                <div class="bar-chart-cell">
                                    <div class="bar" style="width: {{ min($range['percentage'], 100) }}%;"></div>
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
                        <th width="30%">Value</th>
                        <th width="30%">Timestamp</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($data['responses'] as $index => $response)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>
                                @if(isset($response['value']))
                                    {{ number_format($response['value'], 2) }}
                                @else
                                    <em>No value</em>
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