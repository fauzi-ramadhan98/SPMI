import json
import networkx as nx
from networkx.readwrite import json_graph
from pathlib import Path

data = json.loads(Path('graphify-out/graph.json').read_text(encoding='utf-8'))
G = json_graph.node_link_graph(data, edges='links')

targets = ['EvaluationController', 'AuditFindingController', 'PimpinanController', 'ReportController', 'KertasKerjaController', 'AuditInstrumentController']

for term in targets:
    scored = sorted(
        [(sum(1 for w in term.lower().split() if w in G.nodes[n].get('label', '').lower()), n)
         for n in G.nodes()],
        reverse=True
    )
    nid = scored[0][1] if scored and scored[0][0] > 0 else None
    if not nid:
        print(f'== {term}: NOT FOUND')
        continue
    d = G.nodes[nid]
    print(f'== {term} ({nid})')
    print(f'   src={d.get("source_file")}')
    funcs = []
    for neighbor in G.neighbors(nid):
        _raw = G[nid][neighbor]
        edge = next(iter(_raw.values()), {}) if isinstance(G, nx.MultiGraph) else _raw
        rel = edge.get('relation', '')
        nlabel = G.nodes[neighbor].get('label', neighbor)
        if rel == 'calls' or rel == 'method':
            funcs.append(nlabel)
    print(f'   FUNCTIONS: {sorted(set(funcs))}')
    print()
