import json
import networkx as nx
from networkx.readwrite import json_graph
from pathlib import Path

data = json.loads(Path('graphify-out/graph.json').read_text(encoding='utf-8'))
G = json_graph.node_link_graph(data, edges='links')

controllers = {}
for nid, d in G.nodes(data=True):
    label = d.get('label', '')
    if isinstance(label, str) and label.endswith('Controller'):
        controllers[nid] = label

for nid, label in sorted(controllers.items()):
    funcs = set()
    for neighbor in G.neighbors(nid):
        _raw = G[nid][neighbor]
        edge = next(iter(_raw.values()), {}) if isinstance(G, nx.MultiGraph) else _raw
        rel = edge.get('relation', '')
        nlabel = G.nodes[neighbor].get('label', neighbor)
        if rel in ('calls', 'method'):
            funcs.add(nlabel)
    print(f'{label}: {len(funcs)} fungsi -> {sorted(funcs)}')
