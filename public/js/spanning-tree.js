let network = null;
let nodes = null;
let edges = null;
let currentGraph = null;
let animationSteps = [];
let currentStepIndex = 0;
let animationInterval = null;
let isPaused = false;
let animationSpeed = 400;
let fallbackTimer = null;

$(document).ready(function() {
    if (window.defaultGraph) {
        loadGraph(window.defaultGraph);
    }
    setupEventListeners();
    updateStatistics();
});

function setupEventListeners() {
    $('#btnGenerateRandom').on('click', generateRandomGraph);
    $('#btnLoadCustom').on('click', loadCustomGraph);
    $('#btnRunAlgorithm').on('click', runAlgorithm);
    $('#btnPause').on('click', togglePause);
    $('#btnReset').on('click', function() { resetVisualization(true); });
    
    $('#graphDensity').on('input', function() {
        $('#densityValue').text($(this).val());
    });
    
    $('#animationSpeed').on('input', function() {
        const speed = parseInt($(this).val());
        animationSpeed = speed;
        $('#speedValue').text((speed / 1000).toFixed(1) + 's');
    });
}

function loadGraph(graphData) {
    currentGraph = graphData;
    const container = document.getElementById('graphCanvas');
    
    // Konfigurasi Node Awal
    const nodesArray = graphData.vertices.map(vertex => ({
        id: vertex.id,
        label: vertex.label,
        color: {
            background: '#3b82f6', // Blue-500
            border: '#1e40af',     // Blue-800
            highlight: { background: '#60a5fa', border: '#1e3a8a' }
        },
        font: {
            color: '#ffffff',
            size: 28,
            face: 'Inter',
            bold: true,
            strokeWidth: 4,
            strokeColor: '#1e40af'
        },
        borderWidth: 5,
        size: 50,
        shadow: { enabled: true, color: 'rgba(0,0,0,0.4)', size: 20 }
    }));
    
    // Konfigurasi Edge Awal
    const edgesArray = graphData.edges.map((edge, index) => ({
        id: index,
        from: edge.from,
        to: edge.to,
        label: String(edge.weight),
        color: { color: '#64748b' }, // Slate-500
        width: 6,
        font: {
            size: 22,
            color: '#0f172a',
            background: '#ffffff',
            strokeWidth: 0,
            align: 'middle',
            face: 'Inter',
            bold: true
        },
        smooth: { type: 'continuous', roundness: 0.2 },
        weight: edge.weight
    }));
    
    nodes = new vis.DataSet(nodesArray);
    edges = new vis.DataSet(edgesArray);
    
    const options = {
        nodes: { shape: 'circle', size: 50, borderWidth: 5 },
        edges: { width: 6, smooth: { type: 'continuous' } },
        physics: {
            enabled: true,
            barnesHut: {
                gravitationalConstant: -8000,
                centralGravity: 0.3,
                springLength: 200,
                springConstant: 0.04,
                damping: 0.09
            },
            stabilization: { enabled: true, iterations: 100 }
        },
        interaction: { hover: true, zoomView: true, dragView: true }
    };
    
    network = new vis.Network(container, { nodes, edges }, options);
    
    // Matikan physics setelah stabil agar graph tidak goyang saat animasi
    network.once('stabilizationIterationsDone', function() {
        network.setOptions({ physics: false });
    });
    
    updateStartVertexOptions(graphData.vertices);
    updateStatistics();
}

function updateStartVertexOptions(vertices) {
    const select = $('#startVertex');
    select.empty();
    vertices.forEach(vertex => {
        select.append(`<option value="${vertex.id}">${vertex.label}</option>`);
    });
}

function updateStatistics() {
    if (!currentGraph) return;
    $('#statVertices').text(currentGraph.vertices.length);
    $('#statEdges').text(currentGraph.edges.length);
    const totalWeight = currentGraph.edges.reduce((sum, edge) => sum + edge.weight, 0);
    $('#statTotalWeight').text(totalWeight);
}

function generateRandomGraph() {
    const numVertices = parseInt($('#numVertices').val());
    const density = parseFloat($('#graphDensity').val());
    
    if (numVertices < 3 || numVertices > 20) {
        alert('Vertices must be between 3 and 20');
        return;
    }
    
    $('#btnGenerateRandom').html('<i class="fas fa-spinner fa-spin"></i> Generating...').prop('disabled', true);
    
    $.ajax({
        url: window.apiUrls.generateRandom,
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': window.csrfToken },
        data: { vertices: numVertices, density: density },
        success: function(response) {
            if (response.success) {
                loadGraph(response.data);
                resetVisualization(false); // Reset UI tanpa reload graph
            }
        },
        error: function() {
            alert('Failed to generate graph.');
        },
        complete: function() {
            $('#btnGenerateRandom').html('<i class="fas fa-random"></i> Generate Random').prop('disabled', false);
        }
    });
}

function loadCustomGraph() {
    const verticesInput = $('#customVertices').val().trim();
    const edgesInput = $('#customEdges').val().trim();
    
    if (!verticesInput || !edgesInput) {
        alert('Please provide both vertices and edges.');
        return;
    }
    
    try {
        const vertexLabels = verticesInput.split(',').map(v => v.trim());
        const vertices = vertexLabels.map((label, index) => ({ id: index, label: label }));
        
        const labelToId = {};
        vertices.forEach(v => { labelToId[v.label] = v.id; });
        
        const edgeLines = edgesInput.split('\n').filter(line => line.trim());
        const edges = [];
        
        edgeLines.forEach(line => {
            // Format: A-B:5
            const match = line.match(/^([A-Za-z0-9]+)-([A-Za-z0-9]+):(\d+)$/);
            if (match) {
                const from = match[1].trim();
                const to = match[2].trim();
                const weight = parseInt(match[3]);
                
                if (labelToId.hasOwnProperty(from) && labelToId.hasOwnProperty(to)) {
                    edges.push({ from: labelToId[from], to: labelToId[to], weight: weight });
                }
            }
        });
        
        if (edges.length === 0) throw new Error('No valid edges found');
        
        loadGraph({ vertices, edges });
        resetVisualization(false);
        
    } catch (error) {
        alert('Error parsing custom graph. Use format A-B:5');
    }
}

function runAlgorithm() {
    const algorithm = $('#algorithmSelect').val();
    const startVertex = parseInt($('#startVertex').val());
    
    if (!currentGraph) {
        alert('Please load a graph first');
        return;
    }
    
    resetVisualization(false);
    
    $('#btnRunAlgorithm').prop('disabled', true);
    $('#btnPause').prop('disabled', false);
    $('#algorithmStatus').html('<i class="fas fa-spinner fa-spin"></i> Running...').addClass('running').removeClass('completed');
    $('#animationOverlay').fadeIn(300);
    
    // Safety timeout
    fallbackTimer = setTimeout(function() {
        if ($('#algorithmStatus').hasClass('running')) {
            $('#animationOverlay').hide();
            $('#algorithmStatus').html('<i class="fas fa-exclamation-circle"></i> Timeout').removeClass('running');
            $('#btnRunAlgorithm').prop('disabled', false);
        }
    }, 30000);
    
    const url = (algorithm === 'bfs') ? window.apiUrls.runBFS : window.apiUrls.runDFS;
    
    $.ajax({
        url: url,
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': window.csrfToken },
        data: {
            vertices: currentGraph.vertices,
            edges: currentGraph.edges,
            start_vertex: startVertex
        },
        success: function(response) {
            clearTimeout(fallbackTimer);
            
            if (response.success && response.steps && response.steps.length > 0) {
                animationSteps = response.steps;
                $('#totalSteps').text(animationSteps.length);
                startAnimation();
            } else {
                $('#animationOverlay').hide();
                alert('No steps returned from algorithm.');
                resetVisualization(false);
            }
        },
        error: function(xhr, status, error) {
            clearTimeout(fallbackTimer);
            $('#animationOverlay').hide();
            alert('Algorithm execution error: ' + error);
            resetVisualization(false);
        }
    });
}

function startAnimation() {
    currentStepIndex = 0;
    isPaused = false;
    $('#stepsList').empty();
    $('#exploredEdgesBody').empty();
    animateNextStep();
}

function animateNextStep() {
    if (!animationSteps || animationSteps.length === 0) {
        completeAnimation();
        return;
    }
    
    if (currentStepIndex >= animationSteps.length) {
        completeAnimation();
        return;
    }
    
    if (isPaused) return;
    
    const step = animationSteps[currentStepIndex];
    
    // Update UI Progress
    const progress = ((currentStepIndex + 1) / animationSteps.length) * 100;
    $('#progressFill').css('width', progress + '%');
    $('#currentStep').text(currentStepIndex + 1);
    $('#currentStepDescription').text(step.description);
    
    // Core Visualization
    visualizeStep(step);
    addStepToList(step);
    
    if (step.current_edge) {
        addToExploredEdges(step);
    }
    
    currentStepIndex++;
    
    if (animationInterval) clearTimeout(animationInterval);
    animationInterval = setTimeout(animateNextStep, animationSpeed);
}

/**
 * OPTIMIZED VISUALIZATION FUNCTION
 * Menggunakan batch update untuk performa lebih baik.
 */
function visualizeStep(step) {
    const nodeUpdates = [];
    const edgeUpdates = [];

    // 1. Prepare Defaults (Reset state for this frame)
    // Kita tidak me-reset label agar tidak ada flickering teks
    nodes.getIds().forEach(id => {
        nodeUpdates.push({
            id: id,
            color: { background: '#3b82f6', border: '#1e40af' }, // Default Blue
            borderWidth: 5,
            size: 50,
            font: { color: '#ffffff', strokeColor: '#1e40af' }
        });
    });

    edges.getIds().forEach(id => {
        edgeUpdates.push({
            id: id,
            color: { color: '#64748b' }, // Default Slate
            width: 6
        });
    });

    // Helper untuk mencari object update di array (by Reference)
    const getNodeUpdate = (id) => nodeUpdates.find(u => u.id == id);
    const getEdgeUpdate = (id) => edgeUpdates.find(u => u.id == id);

    // 2. Apply VISITED Nodes (Green)
    if (step.visited && step.visited.length > 0) {
        step.visited.forEach(vertexId => {
            const u = getNodeUpdate(vertexId);
            if (u) {
                u.color = { background: '#10b981', border: '#059669' }; // Emerald
                u.font.strokeColor = '#059669';
            }
        });
    }

    // 3. Apply CURRENT Vertex (Red)
    if (step.current_vertex !== null && step.current_vertex !== undefined) {
        const u = getNodeUpdate(step.current_vertex);
        if (u) {
            u.color = { background: '#ef4444', border: '#dc2626' }; // Red
            u.borderWidth = 7;
            u.size = 55;
            u.font.strokeColor = '#dc2626';
        }
    }

    // 4. Apply EXPLORING Edges (Yellow) - Sedang dipertimbangkan
    if (step.exploring && step.exploring.length > 0) {
        step.exploring.forEach(edge => {
            const edgeId = findEdgeId(edge.from, edge.to);
            const u = getEdgeUpdate(edgeId);
            if (u) {
                u.color = { color: '#fbbf24' }; // Amber
                u.width = 8;
            }
        });
    }

    // 5. Apply CURRENT Edge (Orange) - Sedang diproses
    if (step.current_edge) {
        const edgeId = findEdgeId(step.current_edge.from, step.current_edge.to);
        const u = getEdgeUpdate(edgeId);
        if (u) {
            u.color = { color: '#f59e0b' }; // Orange
            u.width = 10;
        }
    }

    // 6. Apply TREE Edges (Green) - Bagian dari Spanning Tree (FINAL LAYER)
    if (step.tree_edges && step.tree_edges.length > 0) {
        step.tree_edges.forEach(edge => {
            const edgeId = findEdgeId(edge.from, edge.to);
            const u = getEdgeUpdate(edgeId);
            if (u) {
                u.color = { color: '#059669' }; // Emerald Strong
                u.width = 9;
            }
        });
    }

    // 7. Commit Updates ke vis.js (Hanya 2 kali pemanggilan DOM interaction)
    nodes.update(nodeUpdates);
    edges.update(edgeUpdates);
}

function findEdgeId(from, to) {
    // Mencari edge ID berdasarkan from-to (undirected)
    const foundEdges = edges.get({
        filter: function(edge) {
            return (edge.from == from && edge.to == to) || (edge.from == to && edge.to == from);
        }
    });
    return foundEdges.length > 0 ? foundEdges[0].id : null;
}

function addStepToList(step) {
    const stepHtml = `
        <div class="step-item active fade-in" style="margin-bottom: 8px; padding: 8px; border-left: 3px solid #3b82f6; background: #f8fafc;">
            <div style="font-weight:bold; font-size:0.85rem; color:#64748b;">Step ${step.step}</div>
            <div class="step-description" style="font-size:0.95rem;">${step.description}</div>
        </div>
    `;
    const list = $('#stepsList');
    list.append(stepHtml);
    list.scrollTop(list[0].scrollHeight);
}

function addToExploredEdges(step) {
    const edge = step.current_edge;
    // Cari label vertex untuk tampilan yang lebih informatif
    const fromNode = nodes.get(edge.from);
    const toNode = nodes.get(edge.to);
    
    if (!fromNode || !toNode) return;

    const rowHtml = `
        <tr class="highlight fade-in">
            <td>${step.step}</td>
            <td><strong>${fromNode.label} ↔ ${toNode.label}</strong></td>
            <td>${edge.weight}</td>
            <td class="text-success"><i class="fas fa-check"></i></td>
        </tr>
    `;
    $('#exploredEdgesBody').append(rowHtml);
}

function completeAnimation() {
    clearTimeout(animationInterval);
    clearTimeout(fallbackTimer);
    animationInterval = null;
    fallbackTimer = null;
    
    $('#animationOverlay').hide();
    
    $('#algorithmStatus')
        .html('<i class="fas fa-check-circle"></i> Completed')
        .removeClass('running')
        .addClass('completed');
    
    const lastStep = animationSteps[animationSteps.length - 1];
    const algorithm = $('#algorithmSelect option:selected').text();
    
    $('#resultAlgorithm').text(algorithm);
    $('#resultEdgeCount').text(lastStep.tree_edges ? lastStep.tree_edges.length : 0);
    $('#resultVertexCount').text(lastStep.visited ? lastStep.visited.length : 0);
    
    // Tampilkan Hasil Edges Spanning Tree
    const edgesList = $('#resultEdgesList');
    edgesList.empty();
    
    if (lastStep.tree_edges) {
        lastStep.tree_edges.forEach((edge, index) => {
            const fromNode = nodes.get(edge.from);
            const toNode = nodes.get(edge.to);
            if(fromNode && toNode) {
                edgesList.append(`
                    <li class="flex justify-between items-center py-2 border-b border-gray-100">
                        <span class="font-medium text-gray-700">${index + 1}. ${fromNode.label} ↔ ${toNode.label}</span>
                        <span class="bg-blue-100 text-blue-800 text-xs font-bold px-2 py-1 rounded">Weight: ${edge.weight}</span>
                    </li>
                `);
            }
        });
    }
    
    $('#resultCard').fadeIn();
    $('#btnRunAlgorithm').prop('disabled', false);
    $('#btnPause').prop('disabled', true).html('<i class="fas fa-pause"></i> Pause');
}

function togglePause() {
    if (!animationSteps.length) return;

    isPaused = !isPaused;
    
    if (isPaused) {
        $('#btnPause').html('<i class="fas fa-play"></i> Resume');
        if (animationInterval) clearTimeout(animationInterval);
    } else {
        $('#btnPause').html('<i class="fas fa-pause"></i> Pause');
        animateNextStep();
    }
}

function resetVisualization(reloadGraph = true) {
    if (animationInterval) clearTimeout(animationInterval);
    if (fallbackTimer) clearTimeout(fallbackTimer);
    
    animationInterval = null;
    fallbackTimer = null;
    animationSteps = [];
    currentStepIndex = 0;
    isPaused = false;
    
    $('#animationOverlay').hide();
    $('#algorithmStatus').html('<i class="fas fa-circle"></i> Ready').removeClass('running completed');
    
    $('#stepsList').html('<div class="empty-state text-center text-gray-400 py-4"><p>Run algorithm to see steps</p></div>');
    $('#exploredEdgesBody').html('<tr><td colspan="4" class="text-center text-muted">No edges processed</td></tr>');
    $('#resultCard').hide();
    $('#progressFill').css('width', '0%');
    $('#currentStep').text('0');
    
    $('#btnRunAlgorithm').prop('disabled', false);
    $('#btnPause').prop('disabled', true).html('<i class="fas fa-pause"></i> Pause');
    
    if (reloadGraph && currentGraph) {
        loadGraph(currentGraph);
    }
}

// Expose ke window untuk debugging jika perlu
window.GraphTraversal = { loadGraph, runAlgorithm, resetVisualization, network, currentGraph };