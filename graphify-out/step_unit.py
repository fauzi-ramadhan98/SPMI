import json
import networkx as nx
from networkx.readwrite import json_graph
from pathlib import Path

data = json.loads(Path('graphify-out/graph.json').read_text(encoding='utf-8'))
G = json_graph.node_link_graph(data, edges='links')

# Find the model Unit node specifically
candidates = [n for n, d in G.nodes(data=True) if d.get('label') == 'Unit' or 'Unit' == d.get('label')]
for nid in candidates:
    d = G.nodes[nid]
    print(f'CANDIDATE {nid} | src={d.get("source_file")}')

for nid in candidates:
    print()
    print(f'NODE: {G.nodes[nid].get("label", nid)}')
    print(f'  source: {G.nodes[nid].get("source_file", "unknown")}')
    print(f'  type: {G.nodes[nid].get("file_type", "unknown")}')
    print(f'  degree: {G.degree(nid)}')
    print('  CONNECTIONS:')
    for neighbor in G.neighbors(nid):
        _raw = G[nid][neighbor]
        edge = next(iter(_raw.values()), {}) if isinstance(G, nx.MultiGraph) else _raw
        nlabel = G.nodes[neighbor].get('label', neighbor)
        rel = edge.get('relation', '')
        conf = edge.get('confidence', '')
        src_file = G.nodes[neighbor].get('source_file', '')
        print(f'    --{rel}--> {nlabel} [{conf}] ({src_file})')
