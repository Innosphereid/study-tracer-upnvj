<div class="question-visualizer text-response-visualizer">
    <!-- Statistics Summary -->
    <div class="statistics-summary">
        <div class="stat-row">
            <div class="stat-item">
                <div class="stat-label">Total Responses</div>
                <div class="stat-value">{{ isset($data['responses']) ? count($data['responses']) : 0 }}</div>
            </div>
            @if(isset($data['stats']))
                <div class="stat-item">
                    <div class="stat-label">Average Length</div>
                    <div class="stat-value">{{ $data['stats']['avg_length'] ?? 'N/A' }} chars</div>
                </div>
                <div class="stat-item">
                    <div class="stat-label">Longest</div>
                    <div class="stat-value">{{ $data['stats']['max_length'] ?? 'N/A' }} chars</div>
                </div>
                <div class="stat-item">
                    <div class="stat-label">Shortest</div>
                    <div class="stat-value">{{ $data['stats']['min_length'] ?? 'N/A' }} chars</div>
                </div>
            @endif
        </div>
    </div>

    <!-- Word Cloud -->
    @if(isset($chart) && !empty($chart))
        <div class="chart-container">
            <h4>Word Cloud</h4>
            <img src="{{ $chart }}" alt="Text Response Word Cloud" class="chart-image">
        </div>
    @endif

    <!-- Common Words -->
    @if(isset($data['common_words']) && count($data['common_words']) > 0)
        <div class="common-words">
            <h4>Most Common Words</h4>
            <table>
                <thead>
                    <tr>
                        <th width="50%">Word</th>
                        <th width="25%">Count</th>
                        <th width="25%">Percentage</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($data['common_words'] as $word)
                        <tr>
                            <td>{{ $word['word'] }}</td>
                            <td>{{ $word['count'] }}</td>
                            <td>{{ number_format($word['percentage'], 1) }}%</td>
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
                        <th width="60%">Response</th>
                        <th width="30%">Timestamp</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($data['responses'] as $index => $response)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>
                                @if(isset($response['value']) && !empty($response['value']))
                                    {{ $response['value'] }}
                                @else
                                    <em>No response provided</em>
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