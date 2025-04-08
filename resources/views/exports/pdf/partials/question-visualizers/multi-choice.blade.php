<div class="question-visualizer multi-choice-visualizer">
    <!-- Statistics Summary -->
    <div class="statistics-summary">
        <div class="stat-row">
            <div class="stat-item">
                <div class="stat-label">Total Responses</div>
                <div class="stat-value">{{ isset($data['responses']) ? count($data['responses']) : 0 }}</div>
            </div>
            @if(isset($data['stats']) && isset($data['stats']['avg_selections']))
                <div class="stat-item">
                    <div class="stat-label">Avg. Selections</div>
                    <div class="stat-value">{{ number_format($data['stats']['avg_selections'], 2) }}</div>
                </div>
            @endif
        </div>
    </div>

    <!-- Chart -->
    @if(isset($chart) && !empty($chart))
        <div class="chart-container">
            <h4>Response Distribution</h4>
            <img src="{{ $chart }}" alt="Multi-Choice Distribution" class="chart-image">
        </div>
    @endif

    <!-- Options Summary -->
    @if(isset($data['options_summary']) && count($data['options_summary']) > 0)
        <div class="options-summary">
            <h4>Options Summary</h4>
            <table>
                <thead>
                    <tr>
                        <th width="50%">Option</th>
                        <th width="15%">Count</th>
                        <th width="15%">Percentage</th>
                        <th width="20%">Visualization</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($data['options_summary'] as $option)
                        <tr>
                            <td>{{ $option['label'] }}</td>
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

    <!-- Other Responses (if applicable) -->
    @if(isset($data['other_responses']) && count($data['other_responses']) > 0)
        <div class="other-responses">
            <h4>Other Responses</h4>
            <table>
                <thead>
                    <tr>
                        <th width="10%">No.</th>
                        <th width="60%">Response</th>
                        <th width="30%">Timestamp</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($data['other_responses'] as $index => $response)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $response['text'] }}</td>
                            <td>{{ isset($response['created_at']) ? \Carbon\Carbon::parse($response['created_at'])->format('M d, Y H:i') : 'N/A' }}</td>
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
                        <th width="60%">Selected Options</th>
                        <th width="30%">Timestamp</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($data['responses'] as $index => $response)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>
                                @if(isset($response['selected_options']) && !empty($response['selected_options']))
                                    <ul class="selected-options-list">
                                        @foreach($response['selected_options'] as $option)
                                            <li>{{ $option }}</li>
                                        @endforeach
                                    </ul>
                                @else
                                    <em>No options selected</em>
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