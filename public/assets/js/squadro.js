/*
 * Améliorations progressives de l'interface (le jeu reste jouable sans JavaScript) :
 *  - aperçu de la case d'arrivée au survol d'une pièce jouable ;
 *  - protection contre le double envoi d'un coup ;
 *  - rafraîchissement automatique quand l'adversaire a joué.
 */
(() => {
    'use strict';

    // Aperçu de la destination
    document.querySelectorAll('.piece--jouable[data-cible]').forEach((piece) => {
        const cible = document.getElementById(piece.dataset.cible);
        if (!cible) return;
        const afficher = () => cible.classList.add('case--cible');
        const masquer = () => cible.classList.remove('case--cible');
        piece.addEventListener('mouseenter', afficher);
        piece.addEventListener('focus', afficher);
        piece.addEventListener('mouseleave', masquer);
        piece.addEventListener('blur', masquer);
    });

    // Un seul envoi par coup
    document.querySelectorAll('[data-formulaire-coup]').forEach((formulaire) => {
        let envoye = false;
        formulaire.addEventListener('submit', (evenement) => {
            if (envoye) {
                evenement.preventDefault();
                return;
            }
            envoye = true;
            formulaire.classList.add('plateau-cadre--envoi');
        });
    });

    // Attente du coup adverse
    const partie = document.querySelector('.partie[data-etat-url]');
    if (partie) {
        const versionAffichee = Number(partie.dataset.version);
        const url = partie.dataset.etatUrl;

        const verifier = async () => {
            if (document.hidden) return;
            try {
                const reponse = await fetch(url, { headers: { Accept: 'application/json' }, cache: 'no-store' });
                if (!reponse.ok) return;
                const { version } = await reponse.json();
                if (version !== versionAffichee) window.location.reload();
            } catch {
                /* réseau indisponible : nouvel essai au prochain intervalle */
            }
        };

        setInterval(verifier, 2500);
        document.addEventListener('visibilitychange', verifier);
    }
})();
