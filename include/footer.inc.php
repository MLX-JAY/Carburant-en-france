
    <a href="#top"><img src="images/haut.png" class="img-fixe" id="imageScroll" alt="Haut de page" /></a>
    <script>
        const image = document.getElementById("imageScroll");

        window.addEventListener("scroll", () => {
            if (window.scrollY > 200) { // apparaît après 200px de scroll
                image.classList.add("visible");
            } else {
                image.classList.remove("visible");
            }
        });
    </script>
    </main>

    <footer>
        <nav class="navigation-pied">
            <ul>
                <li><a href="index.php?index=<?= $index ?>&code_postal=<?= $codePostal ?>&perimetre=<?= $perimetre ?>&carburant=<?= $carburant ?>&tri=<?= $tri ?>&page=<?= $page ?>&afficher=<?= $afficher ?>&style=<?= $style ?>&lang=<?= $lang ?>">Accueil</a></li>
            </ul>
        </nav>
        <address>
            <p>Réalisé par <strong><?= $pageAuthor ?></strong></p>
            <p>L2 Informatique - CY Cergy Paris Université</p>
            <p>Année scolaire 2025/2026</p>
        </address>
    </footer>
</body>
</html>
