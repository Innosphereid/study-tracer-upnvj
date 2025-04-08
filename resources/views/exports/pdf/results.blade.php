@extends('exports.pdf.layouts.pdf-layout')

@section('content')
    <!-- Summary Section -->
    <section id="summary">
        <h1 class="section-title">Summary</h1>
        
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">{{ $questionnaire->title }}</h2>
                <div class="card-meta">
                    @if($questionnaire->description)
                        <p>{{ $questionnaire->description }}</p>
                    @endif
                    <p>
                        <span class="badge badge-primary">{{ $questionnaire->status }}</span>
                        @if($questionnaire->start_date)
                            &middot; Started: {{ \Carbon\Carbon::parse($questionnaire->start_date)->format('M d, Y') }}
                        @endif
                        @if($questionnaire->end_date)
                            &middot; Ended: {{ \Carbon\Carbon::parse($questionnaire->end_date)->format('M d, Y') }}
                        @endif
                    </p>
                </div>
            </div>
            
            <div class="stats-summary">
                <div class="stat-item">
                    <div class="stat-value">{{ $statistics['total_responses'] ?? 0 }}</div>
                    <div class="stat-label">Total Responses</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value">{{ number_format($statistics['completion_rate'] ?? 0, 1) }}%</div>
                    <div class="stat-label">Completion Rate</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value">{{ $statistics['sections_count'] ?? 0 }}</div>
                    <div class="stat-label">Sections</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value">{{ $statistics['questions_count'] ?? 0 }}</div>
                    <div class="stat-label">Questions</div>
                </div>
            </div>
        </div>

        @if(isset($statistics['response_by_date']) && count($statistics['response_by_date']) > 0)
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Response Timeline</h3>
                </div>
                <div class="chart-container">
                    @if(isset($charts['response_timeline']))
                        <img src="{{ $charts['response_timeline'] }}" alt="Response Timeline" class="chart-image">
                    @else
                        <p class="text-center text-muted">No timeline data available</p>
                    @endif
                </div>
            </div>
        @endif
    </section>

    <!-- Table of Contents -->
    <section id="toc">
        <h2 class="section-title">Table of Contents</h2>
        <div class="card">
            <ol>
                <li><a href="#summary">Summary</a></li>
                @foreach($questionnaire->sections as $index => $section)
                    <li>
                        <a href="#section-{{ $section->id }}">{{ $section->title }}</a>
                        @if(isset($section->questions) && count($section->questions) > 0)
                            <ol type="a">
                                @foreach($section->questions as $question)
                                    <li><a href="#question-{{ $question->id }}">{{ $question->title }}</a></li>
                                @endforeach
                            </ol>
                        @endif
                    </li>
                @endforeach
            </ol>
        </div>
    </section>

    <!-- Page break after TOC -->
    <div class="page-break"></div>

    <!-- Sections and Questions -->
    @foreach($questionnaire->sections as $sectionIndex => $section)
        <section id="section-{{ $section->id }}">
            <h2 class="section-title">{{ $sectionIndex + 1 }}. {{ $section->title }}</h2>
            
            @if($section->description)
                <p>{{ $section->description }}</p>
            @endif

            @if(isset($section->questions) && count($section->questions) > 0)
                @foreach($section->questions as $questionIndex => $question)
                    <div class="card" id="question-{{ $question->id }}">
                        <div class="card-header">
                            <h3 class="card-title">
                                {{ $sectionIndex + 1 }}.{{ $questionIndex + 1 }} {{ $question->title }}
                                <span class="question-type question-{{ $question->question_type }}">
                                    {{ ucfirst($question->question_type) }}
                                </span>
                            </h3>
                            @if($question->description)
                                <div class="card-meta">{{ $question->description }}</div>
                            @endif
                        </div>
                        
                        @if(isset($questionData[$question->id]))
                            @php
                                $responseData = $questionData[$question->id];
                                $responseCount = count($responseData['responses'] ?? []);
                            @endphp
                            
                            <div class="card-meta">
                                <strong>{{ $responseCount }}</strong> responses
                            </div>
                            
                            <!-- Visualization based on question type -->
                            @php
                                $visualizerType = $question->question_type;
                                $templatePath = 'exports.pdf.partials.question-visualizers.' . $visualizerType;
                                
                                // Check if a fallback is needed
                                if (!view()->exists($templatePath) && isset($fallbackVisualizerMap[$visualizerType])) {
                                    $visualizerType = $fallbackVisualizerMap[$visualizerType];
                                    $templatePath = 'exports.pdf.partials.question-visualizers.' . $visualizerType;
                                }
                                
                                // Default to 'text' if no template exists
                                if (!view()->exists($templatePath)) {
                                    $visualizerType = 'text';
                                    $templatePath = 'exports.pdf.partials.question-visualizers.text';
                                }
                            @endphp
                            
                            @include($templatePath, [
                                'question' => $question,
                                'data' => $responseData,
                                'chart' => $charts[$question->id] ?? null
                            ])
                        @else
                            <div class="text-center text-muted">
                                <p>No responses for this question</p>
                            </div>
                        @endif
                    </div>
                    
                    <!-- Add page break after a certain number of questions or if it's a complex visualization -->
                    @if($questionIndex > 0 && $questionIndex % 3 == 0 || in_array($question->question_type, ['matrix', 'ranking']))
                        <div class="page-break"></div>
                    @endif
                @endforeach
            @else
                <div class="card">
                    <p class="text-center text-muted">No questions in this section</p>
                </div>
            @endif
        </section>
        
        <!-- Page break between sections -->
        @if(!$loop->last)
            <div class="page-break"></div>
        @endif
    @endforeach
@endsection 