<div class="question-visualizer ranking-visualizer">
    <!-- Statistics Summary -->
    <div class="statistics-summary">
        <div class="stat-row">
            <div class="stat-item">
                <div class="stat-label">Total Responses</div>
                <div class="stat-value">{{ isset($data['responses']) ? count($data['responses']) : 0 }}</div>
            </div>
        </div>
    </div>

    <!-- Chart -->
    @if(isset($chart) && !empty($chart))
        <div class="chart-container">
            <h4>Average Ranking</h4>
            <img src="{{ $chart }}" alt="Ranking Distribution" class="chart-image">
        </div>
    @endif

    <!-- Average Rankings -->
    @if(isset($data['average_rankings']) && count($data['average_rankings']) > 0)
        <div class="average-rankings">
            <h4>Average Position by Item</h4>
            <table>
                <thead>
                    <tr>
                        <th width="40%">Item</th>
                        <th width="20%">Avg. Position</th>
                        <th width="40%">Distribution</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($data['average_rankings'] as $item)
                        <tr>
                            <td>{{ $item['label'] }}</td>
                            <td>{{ number_format($item['average_rank'], 2) }}</td>
                            <td>
                                <div class="bar-chart-cell">
                                    <div class="bar" style="width: {{ min(100 - (($item['average_rank'] - 1) / count($data['average_rankings']) * 100), 100) }}%;"></div>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    <!-- Top Ranked Items -->
    @if(isset($data['top_ranked']) && count($data['top_ranked']) > 0)
        <div class="top-ranked">
            <h4>Most Frequently Top-Ranked Items</h4>
            <table>
                <thead>
                    <tr>
                        <th width="40%">Item</th>
                        <th width="20%">Times Ranked #1</th>
                        <th width="20%">Percentage</th>
                        <th width="20%">Visualization</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($data['top_ranked'] as $item)
                        <tr>
                            <td>{{ $item['label'] }}</td>
                            <td>{{ $item['count'] }}</td>
                            <td>{{ number_format($item['percentage'], 1) }}%</td>
                            <td>
                                <div class="bar-chart-cell">
                                    <div class="bar" style="width: {{ min($item['percentage'], 100) }}%;"></div>
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
                        <th width="60%">Ranking</th>
                        <th width="30%">Timestamp</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($data['responses'] as $index => $response)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>
                                @if(isset($response['ranking']) && !empty($response['ranking']))
                                    <ol class="ranking-list">
                                        @foreach($response['ranking'] as $rank => $item)
                                            <li>{{ $item }}</li>
                                        @endforeach
                                    </ol>
                                @else
                                    <em>No ranking provided</em>
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