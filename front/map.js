// 1. Initialisation de la carte du monde
// On la centre sur l'Europe pour commencer, avec un niveau de zoom initial.
const map = L.map('worldMap').setView([46.2, 2.2], 5);

// 2. Ajout d'un fond de carte (Tile Layer)
// On utilise OpenStreetMap, qui est gratuit et open-source.
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
}).addTo(map);

// 3. Récupération des données depuis notre backend
// fetch() est la manière moderne en JavaScript de faire une requête réseau.
// Remplacez '/api/locations' par l'URL complète de votre API si elle n'est pas sur le même serveur.
fetch('/api/locations')
    .then(response => response.json()) // On convertit la réponse en JSON
    .then(locations => {
        // 4. Boucle sur chaque lieu reçu
        locations.forEach(location => {
            // On vérifie qu'on a bien des coordonnées valides
            if (location.lat && location.lon) {
                // 5. Création du marqueur
                const marker = L.marker([location.lat, location.lon]).addTo(map);

                // 6. Création du contenu de la pop-up (l'information qui s'affiche au clic)
                // On utilise les données associées (nom, adresse)
                const popupContent = `
                    <b>${location.nom}</b><br>
                    ${location.adresse}
                `;

                // 7. Lier la pop-up au marqueur
                marker.bindPopup(popupContent);
            }
        });
    })
    .catch(error => {
        console.error("Erreur lors de la récupération des localisations :", error);
    });
