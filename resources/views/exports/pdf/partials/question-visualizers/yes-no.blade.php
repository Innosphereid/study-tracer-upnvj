<div class="question-visualizer yes-no-visualizer">
    <!-- Statistics Summary -->
    <div class="statistics-summary">
        <div class="stat-row">
            <div class="stat-item">
                <div class="stat-label">Total Responses</div>
                <div class="stat-value">{{ isset($data['responses']) ? count($data['responses']) : 0 }}</div>
            </div>
            <div class="stat-item">
                <div class="stat-label">Yes Responses</div>
                <div class="stat-value">{{ isset($data['yes_count']) ? $data['yes_count'] : 0 }} ({{ isset($data['yes_percentage']) ? number_format($data['yes_percentage'], 1) : 0 }}%)</div>
            </div>
            <div class="stat-item">
                <div class="stat-label">No Responses</div>
                <div class="stat-value">{{ isset($data['no_count']) ? $data['no_count'] : 0 }} ({{ isset($data['no_percentage']) ? number_format($data['no_percentage'], 1) : 0 }}%)</div>
            </div>
        </div>
    </div>

    <!-- Pie Chart - Removed for DomPDF as it can't render complex charts -->
    @if(isset($chart) && !empty($chart))
        <div class="chart-container">
            <h4>Response Distribution</h4>
            <img src="{{ $chart }}" alt="Yes/No Distribution" class="chart-image">
        </div>
    @endif

    <!-- Bar Chart Visualization - Optimized for DomPDF -->
    <div class="yes-no-bars">
        <h4>Response Distribution</h4>
        <table>
            <thead>
                <tr>
                    <th width="20%">Response</th>
                    <th width="15%">Count</th>
                    <th width="15%">Percentage</th>
                    <th width="50%">Visualization</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Yes</td>
                    <td>{{ isset($data['yes_count']) ? $data['yes_count'] : 0 }}</td>
                    <td>{{ isset($data['yes_percentage']) ? number_format($data['yes_percentage'], 1) : 0 }}%</td>
                    <td>
                        <div class="bar-chart-cell">
                            <div class="bar" style="width: {{ isset($data['yes_percentage']) ? min($data['yes_percentage'], 100) : 0 }}%;"></div>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>No</td>
                    <td>{{ isset($data['no_count']) ? $data['no_count'] : 0 }}</td>
                    <td>{{ isset($data['no_percentage']) ? number_format($data['no_percentage'], 1) : 0 }}%</td>
                    <td>
                        <div class="bar-chart-cell">
                            <div class="bar" style="width: {{ isset($data['no_percentage']) ? min($data['no_percentage'], 100) : 0 }}%;"></div>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Response Details - Limited for better PDF rendering -->
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
                    @php
                    // Limit the number of responses shown to prevent massive PDFs
                    $maxResponsesToShow = min(count($data['responses']), 10);
                    @endphp
                    
                    @for($i = 0; $i < $maxResponsesToShow; $i++)
                        @php
                        $response = $data['responses'][$i];
                        @endphp
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td>{{ $response['value'] ? 'Yes' : 'No' }}</td>
                            <td>{{ isset($response['created_at']) ? \Carbon\Carbon::parse($response['created_at'])->format('M d, Y H:i') : 'N/A' }}</td>
                        </tr>
                    @endfor
                    
                    @if(count($data['responses']) > $maxResponsesToShow)
                        <tr>
                            <td colspan="3" class="text-center">
                                <em>... and {{ count($data['responses']) - $maxResponsesToShow }} more responses</em>
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    @endif
</div> 