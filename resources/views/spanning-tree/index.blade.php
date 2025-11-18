<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Graph Traversal Visualizer</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://unpkg.com/vis-network@9.1.2/dist/dist/vis-network.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body>
    <header class="header">
        <div class="container">
            <div class="header-content">
                <div class="logo">
                    <i class="fas fa-project-diagram"></i>
                    <span>Graph Traversal Visualizer</span>
                </div>
                <nav class="nav-menu">
                    <a href="#" class="nav-link active"><i class="fas fa-home"></i> Home</a>
                    <a href="#" class="nav-link"><i class="fas fa-info-circle"></i> About</a>
                </nav>
            </div>
        </div>
    </header>

    <main class="main-content">
        <div class="container">
            <div class="page-header">
                <h1 class="page-title"><i class="fas fa-project-diagram"></i> Graph Traversal Visualizer</h1>
                <p class="page-subtitle">Visualisasi BFS dan DFS</p>
            </div>

            <div class="content-wrapper">
                <div class="sidebar-left">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-random"></i> Generate Graph</h3>
                        </div>
                        <div class="card-body">
                            <div class="form-section">
                                <h4 class="section-title">Random Graph</h4>
                                <div class="form-group">
                                    <label><i class="fas fa-circle-nodes"></i> Vertices</label>
                                    <input type="number" id="numVertices" class="form-control" value="6" min="3" max="20">
                                </div>
                                <div class="form-group">
                                    <label><i class="fas fa-project-diagram"></i> Density</label>
                                    <input type="range" id="graphDensity" class="form-range" value="0.4" min="0.1" max="1" step="0.1">
                                    <div class="range-value"><span id="densityValue">0.4</span></div>
                                </div>
                                <button id="btnGenerateRandom" class="btn btn-primary btn-block">
                                    <i class="fas fa-random"></i> Generate Random
                                </button>
                            </div>
                            <div class="divider"></div>
                            <div class="form-section">
                                <h4 class="section-title">Custom Graph</h4>
                                <div class="form-group">
                                    <label><i class="fas fa-circle-nodes"></i> Vertices</label>
                                    <input type="text" id="customVertices" class="form-control" placeholder="A, B, C, D">
                                </div>
                                <div class="form-group">
                                    <label><i class="fas fa-arrows-alt-h"></i> Edges</label>
                                    <textarea id="customEdges" class="form-control" placeholder="A-B:5&#10;B-C:3"></textarea>
                                </div>
                                <button id="btnLoadCustom" class="btn btn-secondary btn-block">
                                    <i class="fas fa-upload"></i> Load Custom
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="card mt-3">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-chart-bar"></i> Statistics</h3>
                        </div>
                        <div class="card-body">
                            <div class="stat-item">
                                <span class="stat-label">Vertices:</span>
                                <span class="stat-value" id="statVertices">6</span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-label">Edges:</span>
                                <span class="stat-value" id="statEdges">7</span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-label">Total Weight:</span>
                                <span class="stat-value" id="statTotalWeight">24</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="main-visualization">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-play-circle"></i> Algorithm</h3>
                            <div class="status-badge" id="algorithmStatus">
                                <i class="fas fa-circle"></i> Ready
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label><i class="fas fa-code-branch"></i> Algorithm</label>
                                <select id="algorithmSelect" class="form-control">
                                    <option value="bfs">BFS (Breadth-First Search)</option>
                                    <option value="dfs">DFS (Depth-First Search)</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label><i class="fas fa-location-arrow"></i> Start Vertex</label>
                                <select id="startVertex" class="form-control"></select>
                            </div>
                            <div class="form-group">
                                <label><i class="fas fa-gauge-high"></i> Speed</label>
                                <input type="range" id="animationSpeed" class="form-range" value="600" min="300" max="2000" step="100">
                                <div class="range-value"><span id="speedValue">0.6s</span></div>
                            </div>
                            <div class="btn-group">
                                <button id="btnRunAlgorithm" class="btn btn-success btn-lg">
                                    <i class="fas fa-play"></i> Run
                                </button>
                                <button id="btnPause" class="btn btn-warning btn-lg" disabled>
                                    <i class="fas fa-pause"></i> Pause
                                </button>
                            </div>
                            <button id="btnReset" class="btn btn-danger btn-block mt-3">
                                <i class="fas fa-redo"></i> Reset
                            </button>
                        </div>
                    </div>
                    <div class="card mt-3">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-project-diagram"></i> Graph</h3>
                        </div>
                        <div class="card-body p-0">
                            <div id="graphCanvas" class="graph-canvas"></div>
                        </div>
                    </div>
                    <div class="legend-card mt-3">
                        <h4 class="legend-title"><i class="fas fa-info-circle"></i> Legend</h4>
                        <div class="legend-items">
                            <div class="legend-item">
                                <span class="legend-color" style="background: #3b82f6;"></span>
                                <span>Unvisited</span>
                            </div>
                            <div class="legend-item">
                                <span class="legend-color" style="background: #10b981;"></span>
                                <span>Visited</span>
                            </div>
                            <div class="legend-item">
                                <span class="legend-color" style="background: #ef4444;"></span>
                                <span>Current</span>
                            </div>
                            <div class="legend-item">
                                <span class="legend-line" style="background: #64748b;"></span>
                                <span>Default</span>
                            </div>
                            <div class="legend-item">
                                <span class="legend-line" style="background: #f59e0b;"></span>
                                <span>Current Edge</span>
                            </div>
                            <div class="legend-item">
                                <span class="legend-line" style="background: #059669;"></span>
                                <span>Tree Edge</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="sidebar-right">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-list-ol"></i> Steps</h3>
                        </div>
                        <div class="card-body">
                            <div id="stepsList" class="steps-list">
                                <div class="empty-state">
                                    <i class="fas fa-info-circle"></i>
                                    <p>Run algorithm</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card mt-3">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-table"></i> Explored Edges</h3>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Step</th>
                                            <th>Edge</th>
                                            <th>Weight</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody id="exploredEdgesBody">
                                        <tr><td colspan="4" class="text-center text-muted">No edges</td></tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div id="resultCard" class="card mt-3" style="display: none;">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-trophy"></i> Result</h3>
                        </div>
                        <div class="card-body">
                            <div class="result-summary">
                                <div class="result-item">
                                    <span class="result-label">Algorithm:</span>
                                    <span class="result-value" id="resultAlgorithm">-</span>
                                </div>
                                <div class="result-item highlight">
                                    <span class="result-label">Visited:</span>
                                    <span class="result-value" id="resultVertexCount">0</span>
                                </div>
                                <div class="result-item">
                                    <span class="result-label">Edges:</span>
                                    <span class="result-value" id="resultEdgeCount">0</span>
                                </div>
                            </div>
                            <div class="result-edges mt-3">
                                <h5>Tree Edges:</h5>
                                <ul id="resultEdgesList" class="edges-list"></ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <div id="animationOverlay" class="animation-overlay">
        <div class="overlay-content">
            <h3><i class="fas fa-spinner fa-spin"></i> Running...</h3>
            <div id="currentStepDescription">Initializing...</div>
            <div class="progress-bar">
                <div id="progressFill" class="progress-fill" style="width: 0%;"></div>
            </div>
            <div class="progress-text">
                Step <span id="currentStep">0</span> of <span id="totalSteps">0</span>
            </div>
        </div>
    </div>

    <footer class="footer">
        <div class="container">
            <p>&copy; 2025 Graph Traversal Visualizer</p>
        </div>
    </footer>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://unpkg.com/vis-network@9.1.2/dist/vis-network.min.js"></script>
    <script>
        window.defaultGraph = @json($defaultGraph);
        window.csrfToken = '{{ csrf_token() }}';
        window.apiUrls = {
            generateRandom: '{{ route('spanning-tree.generate-random') }}',
            runBFS: '{{ route('spanning-tree.run-bfs') }}',
            runDFS: '{{ route('spanning-tree.run-dfs') }}'
        };
    </script>
    <script src="{{ asset('js/spanning-tree.js') }}"></script>
</body>
</html>