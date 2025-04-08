<div class="question-visualizer file-upload-visualizer">
    <!-- Statistics Summary -->
    <div class="statistics-summary">
        <div class="stat-row">
            <div class="stat-item">
                <div class="stat-label">Total Responses</div>
                <div class="stat-value">{{ isset($data['responses']) ? count($data['responses']) : 0 }}</div>
            </div>
            
            @if(isset($data['stats']))
                @if(isset($data['stats']['total_files']))
                    <div class="stat-item">
                        <div class="stat-label">Total Files</div>
                        <div class="stat-value">{{ $data['stats']['total_files'] }}</div>
                    </div>
                @endif
                
                @if(isset($data['stats']['avg_files_per_response']))
                    <div class="stat-item">
                        <div class="stat-label">Avg. Files per Response</div>
                        <div class="stat-value">{{ number_format($data['stats']['avg_files_per_response'], 2) }}</div>
                    </div>
                @endif
            @endif
        </div>
    </div>

    <!-- File Type Distribution -->
    @if(isset($data['file_types']) && count($data['file_types']) > 0)
        <div class="file-types-summary">
            <h4>File Type Distribution</h4>
            <table>
                <thead>
                    <tr>
                        <th width="40%">File Type</th>
                        <th width="20%">Count</th>
                        <th width="20%">Percentage</th>
                        <th width="20%">Visualization</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($data['file_types'] as $type)
                        <tr>
                            <td>{{ $type['label'] }}</td>
                            <td>{{ $type['count'] }}</td>
                            <td>{{ number_format($type['percentage'], 1) }}%</td>
                            <td>
                                <div class="bar-chart-cell">
                                    <div class="bar" style="width: {{ min($type['percentage'], 100) }}%;"></div>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    <!-- File Size Distribution -->
    @if(isset($data['file_size_distribution']) && count($data['file_size_distribution']) > 0)
        <div class="file-size-distribution">
            <h4>File Size Distribution</h4>
            <table>
                <thead>
                    <tr>
                        <th width="40%">Size Range</th>
                        <th width="20%">Count</th>
                        <th width="20%">Percentage</th>
                        <th width="20%">Visualization</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($data['file_size_distribution'] as $range)
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
            <h4>Files Uploaded ({{ isset($data['stats']['total_files']) ? $data['stats']['total_files'] : 0 }})</h4>
            
            <table>
                <thead>
                    <tr>
                        <th width="10%">No.</th>
                        <th width="40%">File Name</th>
                        <th width="20%">File Type</th>
                        <th width="10%">Size</th>
                        <th width="20%">Uploaded On</th>
                    </tr>
                </thead>
                <tbody>
                    @php $fileCount = 0; @endphp
                    @foreach($data['responses'] as $responseIndex => $response)
                        @if(isset($response['files']) && is_array($response['files']))
                            @foreach($response['files'] as $file)
                                @php $fileCount++; @endphp
                                <tr>
                                    <td>{{ $fileCount }}</td>
                                    <td>{{ $file['name'] ?? 'Unnamed file' }}</td>
                                    <td>{{ $file['type'] ?? 'Unknown' }}</td>
                                    <td>{{ isset($file['size']) ? $file['size_formatted'] : 'Unknown' }}</td>
                                    <td>{{ isset($response['created_at']) ? \Carbon\Carbon::parse($response['created_at'])->format('M d, Y H:i') : 'N/A' }}</td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="5" class="text-center">No files for this response</td>
                            </tr>
                        @endif
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div> 