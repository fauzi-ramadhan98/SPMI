import json
import networkx as nx
from networkx.readwrite import json_graph
from pathlib import Path

data = json.loads(Path('graphify-out/graph.json').read_text(encoding='utf-8'))
print('graph type:', data.get('directed'), '| keys:', list(data.keys())[:8])
G = json_graph.node_link_graph(data, edges='links')

nid = 'app_http_controllers_admin_evaluationcontroller_evaluationcontroller'
print('degree:', G.degree(nid), 'typeof G:', type(G).__name__)
from collections import Counter
rels = Counter()
for neighbor in G.neighbors(nid):
    _raw = G[nid][neighbor]
    if isinstance(G, nx.MultiGraph):
        e = next(iter(_raw.values()), {})
    else:
        e = _raw
    rels[e.get('relation', 'NONE')] += 1
print('relation counts:', dict(rels))