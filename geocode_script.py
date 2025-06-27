import pandas as pd
from geopy.geocoders import Nominatim
from geopy.extra.rate_limiter import RateLimiter
import time

# --- CONFIGURATION ---
# Fichier d'entrée contenant les adresses
input_filename = 'adresses_a_geocoder.csv'
# Fichier de sortie où les résultats seront sauvegardés
output_filename = 'adresses_avec_coordonnees.csv'
# --- FIN CONFIGURATION ---


# 1. Initialisation du service de géocodage (Nominatim)
# Il est TRES IMPORTANT de spécifier un "user_agent" unique pour votre application.
# Remplacez "mon_app_de_geocodage" par un nom qui vous est propre.
geolocator = Nominatim(user_agent="mon_app_de_geocodage/1.0")

# 2. Ajout d'un délai pour respecter la politique d'utilisation de Nominatim
# Nominatim est un service gratuit, il ne faut pas le surcharger.
# Cette ligne garantit que nous ne ferons pas plus d'1 requête par seconde.
geocode = RateLimiter(geolocator.geocode, min_delay_seconds=1)

# 3. Chargement du fichier CSV avec pandas
print(f"Chargement du fichier d'adresses : {input_filename}")
try:
    df = pd.read_csv(input_filename, delimiter=';') 
except FileNotFoundError:
    print(f"ERREUR : Le fichier '{input_filename}' n'a pas été trouvé. Assurez-vous qu'il est dans le même dossier que le script.")
    exit()

# 4. Ajout des colonnes pour la latitude et la longitude si elles n'existent pas
if 'latitude' not in df.columns:
    df['latitude'] = None
if 'longitude' not in df.columns:
    df['longitude'] = None

print("Début du processus de géocodage...")

# 5. Itération sur chaque ligne du fichier
for index, row in df.iterrows():
    # On ne traite que les lignes qui n'ont pas encore été géocodées
    if pd.isna(row['latitude']):
        # Construction de l'adresse complète pour une meilleure précision
        address = f"{row['Adresse']}, {row['CodePostal']} {row['Ville']}, {row['Pays']}"
        print(f"Géocodage de l'adresse (ligne {index + 1}) : {address}")

        try:
            # Appel au service de géocodage
            location = geocode(address, language='fr') # On peut spécifier la langue

            if location:
                # Si une localisation est trouvée, on met à jour le DataFrame
                df.loc[index, 'latitude'] = location.latitude
                df.loc[index, 'longitude'] = location.longitude
                print(f"  -> Trouvé : ({location.latitude}, {location.longitude})")
            else:
                # Si aucune localisation n'est trouvée
                print("  -> Adresse non trouvée.")

        except Exception as e:
            print(f"  -> Une erreur est survenue : {e}")
            
# Le délai de 1 seconde est géré automatiquement par le RateLimiter, pas besoin d'ajouter time.sleep()

# 6. Sauvegarde des résultats dans un nouveau fichier CSV
print("\nGéocodage terminé.")
df.to_csv(output_filename, index=False, encoding='utf-8-sig')
print(f"Les résultats ont été sauvegardés dans : {output_filename}")
