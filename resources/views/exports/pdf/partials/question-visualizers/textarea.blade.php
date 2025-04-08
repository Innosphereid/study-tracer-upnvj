<div class="question-visualizer textarea-visualizer">
    <!-- Statistics Summary -->
    <div class="statistics-summary">
        <div class="stat-row">
            <div class="stat-item">
                <div class="stat-label">Total Responses</div>
                <div class="stat-value">{{ isset($data['responses']) ? count($data['responses']) : 0 }}</div>
            </div>
            @if(isset($data['stats']) && isset($data['stats']['avg_length']))
                <div class="stat-item">
                    <div class="stat-label">Avg. Length</div>
                    <div class="stat-value">{{ $data['stats']['avg_length'] }} chars</div>
                </div>
            @endif
        </div>
    </div>

    <!-- Most Common Words/Phrases (if available) -->
    @if(isset($data['common_words']) && count($data['common_words']) > 0)
        <div class="common-words">
            <h4>Common Words/Phrases</h4>
            <table>
                <thead>
                    <tr>
                        <th width="60%">Word/Phrase</th>
                        <th width="20%">Count</th>
                        <th width="20%">Percentage</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($data['common_words'] as $word)
                        <tr>
                            <td>{{ $word['text'] }}</td>
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
                                @if(isset($response['text']) && !empty($response['text']))
                                    <div class="textarea-response">{{ $response['text'] }}</div>
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