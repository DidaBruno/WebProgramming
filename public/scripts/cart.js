const tbody = document.querySelector('#kosarica-tablica tbody');

let kosarica = JSON.parse(localStorage.getItem('kosarica')) || [];

prikaziKosaricu();

function prikaziKosaricu() {

    tbody.innerHTML = '';

    kosarica.forEach((film, index) => {

        const row = document.createElement('tr');

        row.innerHTML = `
            <td>${film.title}</td>
            <td>${film.year}</td>
            <td>${film.genre}</td>

            <td>
                <button onclick="ukloniFilm(${index})">
                    Remove
                </button>
            </td>
        `;

        tbody.appendChild(row);
    });
}

function ukloniFilm(index) {

    kosarica.splice(index, 1);

    localStorage.setItem('kosarica', JSON.stringify(kosarica));

    prikaziKosaricu();
}

document.getElementById('potvrdi').addEventListener('click', () => {

    const brojFilmova = kosarica.length;

    if (brojFilmova === 0) {

        alert('Košarica je prazna!');

        return;
    }

    alert(
        `Uspješno ste dodali ${brojFilmova} film(a) u svoju košaricu za vikend maraton!`
    );

    localStorage.removeItem('kosarica');

    kosarica = [];

    prikaziKosaricu();
});