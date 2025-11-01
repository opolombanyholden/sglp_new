<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Débogage QR Code PDF - SGLP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <h1>🔧 Débogage QR Code PDF</h1>
        <div class="alert alert-info">
            Outil de diagnostic pour les problèmes d'affichage des QR codes dans les PDF.
        </div>
        
        <div class="card">
            <div class="card-body">
                <div class="mb-3">
                    <label for="dossier_id" class="form-label">ID Dossier (optionnel)</label>
                    <input type="number" class="form-control" id="dossier_id">
                </div>
                <button class="btn btn-primary" onclick="lancerTest()">Lancer le Test</button>
            </div>
        </div>
        
        <div id="results" class="mt-4" style="display: none;">
            <div class="card">
                <div class="card-header">Résultats</div>
                <div class="card-body">
                    <pre id="results-content"></pre>
                </div>
            </div>
        </div>
    </div>

    <script>
        function lancerTest() {
            const dossierId = document.getElementById("dossier_id").value;
            const url = "/debug/qr-code/diagnostic" + (dossierId ? "?dossier_id=" + dossierId : "");
            
            fetch(url)
                .then(response => response.json())
                .then(data => {
                    document.getElementById("results").style.display = "block";
                    document.getElementById("results-content").textContent = JSON.stringify(data, null, 2);
                })
                .catch(error => console.error("Erreur:", error));
        }
    </script>
</body>
</html>