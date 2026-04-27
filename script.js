
let sviFilmovi;

// dohvaćanje svih filmova
fetch('movies.csv')
    .then(res => res.text())
    .then(csv => {
        const rezultat = Papa.parse(csv, {
            header: true,
            skipEmptyLines: true
        });

        const filmovi = rezultat.data.map(film => ({
            title: film.Naslov,
            year: Number(film.Godina),
            genre: film.Zanr,
            duration: Number(film.Trajanje_min),
            rating: Number(film.Ocjena),
            director: film.Redatelj,
            country: film.Zemlja_porijekla
                ?.split('/')
                .map(c => c.trim()) || []
        }));

        sviFilmovi = filmovi;

        prikaziTablicu(filmovi);
    });

// prikazivanje tablice u html-u
function prikaziTablicu(filmovi) {
    const tbody = document.querySelector('#filmovi-tablica tbody');
    tbody.innerHTML = ''; // očisti ako postoji

    for (const film of filmovi) {
        const row = document.createElement('tr');

        row.innerHTML = `
            <td>${film.title}</td>
            <td>${film.year}</td>
            <td>${film.genre}</td>
            <td>${film.duration}</td>
            <td>${film.country.join(', ')}</td>
            <td>${film.rating}</td>
        `;

        tbody.appendChild(row);
    }
}

// filtriranje
document.getElementById('primjeni-filtere').addEventListener('click', () => {
    const genre = document.getElementById('filter-genre').value;
    const yearFrom = Number(document.getElementById('filter-year-from').value);
    const yearTo = Number(document.getElementById('filter-year-to').value);
    const country = document.getElementById('filter-country').value;

    let filtrirani = sviFilmovi;

    // filter genre
    if (genre) {
        filtrirani = filtrirani.filter(film =>
            film.genre.includes(genre)
        );
    }

    // filter year
    if (!isNaN(yearFrom) && yearFrom > 0) {
        filtrirani = filtrirani.filter(film =>
            film.year >= yearFrom
        );
    }
    if (!isNaN(yearTo) && yearTo > 0) {
        filtrirani = filtrirani.filter(film =>
            film.year <= yearTo
        );
    }

    // filter country
    if (country) {
        filtrirani = filtrirani.filter(film =>
            film.country.includes(country)
        );
    }

    prikaziTablicu(filtrirani);
});

// sortiranje
document.getElementById('sort').addEventListener('change', (e) => {
    let sortirani = [...sviFilmovi];

    switch (e.target.value) {

        case 'year-asc':
            sortirani.sort((a, b) => a.year - b.year);
            break;

        case 'year-desc':
            sortirani.sort((a, b) => b.year - a.year);
            break;

        case 'rating-asc':
            sortirani.sort((a, b) => a.rating - b.rating);
            break;

        case 'rating-desc':
            sortirani.sort((a, b) => b.rating - a.rating);
            break;

        default:
            sortirani = sviFilmovi;
    }

    prikaziTablicu(sortirani);
});