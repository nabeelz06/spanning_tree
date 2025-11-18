<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SpanningTreeController extends Controller
{
    public function index()
    {
        $defaultGraph = $this->generateDefaultGraph();
        return view('spanning-tree.index', ['defaultGraph' => $defaultGraph]);
    }

    public function generateRandomGraph(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'vertices' => 'required|integer|min:3|max:20',
            'density' => 'required|numeric|min:0.1|max:1'
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Invalid input', 'errors' => $validator->errors()], 422);
        }

        $numVertices = $request->vertices;
        $density = $request->density;

        $vertices = [];
        for ($i = 0; $i < $numVertices; $i++) {
            $vertices[] = ['id' => $i, 'label' => chr(65 + $i)];
        }

        $edges = [];
        $maxEdges = ($numVertices * ($numVertices - 1)) / 2;
        $targetEdges = (int)($maxEdges * $density);
        
        $used = [0];
        $unused = range(1, $numVertices - 1);
        
        while (count($unused) > 0) {
            $fromVertex = $used[array_rand($used)];
            $toVertex = $unused[array_rand($unused)];
            
            $weight = rand(1, 20);
            $edges[] = ['from' => $fromVertex, 'to' => $toVertex, 'weight' => $weight];
            
            $used[] = $toVertex;
            $unused = array_values(array_diff($unused, [$toVertex]));
        }

        while (count($edges) < $targetEdges && count($edges) < $maxEdges) {
            $from = rand(0, $numVertices - 1);
            $to = rand(0, $numVertices - 1);
            
            if ($from !== $to) {
                $exists = false;
                foreach ($edges as $edge) {
                    if (($edge['from'] == $from && $edge['to'] == $to) || ($edge['from'] == $to && $edge['to'] == $from)) {
                        $exists = true;
                        break;
                    }
                }
                
                if (!$exists) {
                    $weight = rand(1, 20);
                    $edges[] = ['from' => $from, 'to' => $to, 'weight' => $weight];
                }
            }
        }

        return response()->json(['success' => true, 'data' => ['vertices' => $vertices, 'edges' => $edges]]);
    }

    public function runBFSAlgorithm(Request $request)
    {
        $vertices = $request->vertices;
        $edges = $request->edges;
        $startVertex = $request->start_vertex ?? 0;

        $steps = $this->bfsAlgorithm($vertices, $edges, $startVertex);

        return response()->json(['success' => true, 'algorithm' => 'BFS', 'steps' => $steps]);
    }

    public function runDFSAlgorithm(Request $request)
    {
        $vertices = $request->vertices;
        $edges = $request->edges;
        $startVertex = $request->start_vertex ?? 0;

        $steps = $this->dfsAlgorithm($vertices, $edges, $startVertex);

        return response()->json(['success' => true, 'algorithm' => 'DFS', 'steps' => $steps]);
    }

    private function generateDefaultGraph()
    {
        return [
            'vertices' => [
                ['id' => 0, 'label' => 'A'],
                ['id' => 1, 'label' => 'B'],
                ['id' => 2, 'label' => 'C'],
                ['id' => 3, 'label' => 'D'],
                ['id' => 4, 'label' => 'E'],
                ['id' => 5, 'label' => 'F'],
            ],
            'edges' => [
                ['from' => 0, 'to' => 1, 'weight' => 6],
                ['from' => 0, 'to' => 2, 'weight' => 3],
                ['from' => 1, 'to' => 2, 'weight' => 2],
                ['from' => 1, 'to' => 3, 'weight' => 5],
                ['from' => 2, 'to' => 4, 'weight' => 4],
                ['from' => 3, 'to' => 4, 'weight' => 1],
                ['from' => 4, 'to' => 5, 'weight' => 3],
            ]
        ];
    }

    private function bfsAlgorithm($vertices, $edges, $startVertex)
    {
        $steps = [];
        $visited = [];
        $queue = [$startVertex];
        $visitOrder = [];
        $stepNumber = 0;

        $adjacency = [];
        foreach ($vertices as $vertex) {
            $adjacency[$vertex['id']] = [];
        }
        foreach ($edges as $edge) {
            $adjacency[$edge['from']][] = ['vertex' => $edge['to'], 'weight' => $edge['weight']];
            $adjacency[$edge['to']][] = ['vertex' => $edge['from'], 'weight' => $edge['weight']];
        }

        $steps[] = [
            'step' => $stepNumber++,
            'description' => "BFS dimulai dari " . chr(65 + $startVertex),
            'visited' => [],
            'queue' => [$startVertex],
            'current_vertex' => null,
            'current_edge' => null,
            'tree_edges' => [],
            'exploring' => []
        ];

        $treeEdges = [];
        
        while (!empty($queue)) {
            $currentVertex = array_shift($queue);
            if (in_array($currentVertex, $visited)) continue;

            $visited[] = $currentVertex;
            $visitOrder[] = $currentVertex;
            $currentLabel = chr(65 + $currentVertex);

            $neighbors = $adjacency[$currentVertex];
            usort($neighbors, function($a, $b) { return $a['vertex'] - $b['vertex']; });

            $newNeighbors = [];
            foreach ($neighbors as $neighbor) {
                $neighborVertex = $neighbor['vertex'];
                if (!in_array($neighborVertex, $visited) && !in_array($neighborVertex, $queue)) {
                    $queue[] = $neighborVertex;
                    $newNeighbors[] = chr(65 + $neighborVertex);
                    $treeEdges[] = ['from' => $currentVertex, 'to' => $neighborVertex, 'weight' => $neighbor['weight']];
                }
            }

            // HANYA 1 STEP per vertex (bukan per neighbor)
            if (!empty($newNeighbors)) {
                $steps[] = [
                    'step' => $stepNumber++,
                    'description' => "Kunjungi {$currentLabel}, tambahkan: " . implode(", ", $newNeighbors),
                    'visited' => $visited,
                    'queue' => $queue,
                    'current_vertex' => $currentVertex,
                    'current_edge' => null,
                    'tree_edges' => $treeEdges,
                    'exploring' => []
                ];
            } else {
                $steps[] = [
                    'step' => $stepNumber++,
                    'description' => "Kunjungi {$currentLabel} (tidak ada neighbor baru)",
                    'visited' => $visited,
                    'queue' => $queue,
                    'current_vertex' => $currentVertex,
                    'current_edge' => null,
                    'tree_edges' => $treeEdges,
                    'exploring' => []
                ];
            }
        }

        $visitOrderStr = implode(" → ", array_map(function($v) { return chr(65 + $v); }, $visitOrder));
        $steps[] = [
            'step' => $stepNumber,
            'description' => "Selesai! Urutan: {$visitOrderStr}",
            'visited' => $visited,
            'queue' => [],
            'current_vertex' => null,
            'current_edge' => null,
            'tree_edges' => $treeEdges,
            'exploring' => [],
            'completed' => true
        ];

        return $steps;
    }

    private function dfsAlgorithm($vertices, $edges, $startVertex)
    {
        $steps = [];
        $visited = [];
        $stack = [$startVertex];
        $visitOrder = [];
        $treeEdges = [];
        $stepNumber = 0;

        $adjacency = [];
        foreach ($vertices as $vertex) {
            $adjacency[$vertex['id']] = [];
        }
        foreach ($edges as $edge) {
            $adjacency[$edge['from']][] = ['vertex' => $edge['to'], 'weight' => $edge['weight']];
            $adjacency[$edge['to']][] = ['vertex' => $edge['from'], 'weight' => $edge['weight']];
        }

        $steps[] = [
            'step' => $stepNumber++,
            'description' => "DFS dimulai dari vertex " . chr(65 + $startVertex),
            'visited' => [],
            'stack' => [$startVertex],
            'current_vertex' => null,
            'current_edge' => null,
            'tree_edges' => [],
            'exploring' => []
        ];

        while (!empty($stack)) {
            $currentVertex = array_pop($stack);
            
            if (in_array($currentVertex, $visited)) continue;

            $visited[] = $currentVertex;
            $visitOrder[] = $currentVertex;

            $currentLabel = chr(65 + $currentVertex);

            $steps[] = [
                'step' => $stepNumber++,
                'description' => "Kunjungi vertex {$currentLabel}",
                'visited' => $visited,
                'stack' => $stack,
                'current_vertex' => $currentVertex,
                'current_edge' => null,
                'tree_edges' => $treeEdges,
                'exploring' => []
            ];

            $neighbors = $adjacency[$currentVertex];
            usort($neighbors, function($a, $b) { return $b['vertex'] - $a['vertex']; });

            foreach ($neighbors as $neighbor) {
                $neighborVertex = $neighbor['vertex'];
                
                if (!in_array($neighborVertex, $visited) && !in_array($neighborVertex, $stack)) {
                    $stack[] = $neighborVertex;
                    
                    $treeEdges[] = ['from' => $currentVertex, 'to' => $neighborVertex, 'weight' => $neighbor['weight']];

                    $neighborLabel = chr(65 + $neighborVertex);

                    $steps[] = [
                        'step' => $stepNumber++,
                        'description' => "Push {$neighborLabel} ke stack dari {$currentLabel}",
                        'visited' => $visited,
                        'stack' => $stack,
                        'current_vertex' => $currentVertex,
                        'current_edge' => ['from' => $currentVertex, 'to' => $neighborVertex, 'weight' => $neighbor['weight']],
                        'tree_edges' => $treeEdges,
                        'exploring' => []
                    ];
                }
            }
        }

        $visitOrderStr = implode(" → ", array_map(function($v) { return chr(65 + $v); }, $visitOrder));
        $steps[] = [
            'step' => $stepNumber,
            'description' => "DFS selesai! Urutan: {$visitOrderStr}",
            'visited' => $visited,
            'stack' => [],
            'current_vertex' => null,
            'current_edge' => null,
            'tree_edges' => $treeEdges,
            'exploring' => [],
            'completed' => true
        ];

        return $steps;
    }
}