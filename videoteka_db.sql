-- Videoteka baza podataka

-- sluzi za kreiranje baze podataka
CREATE DATABASE IF NOT EXISTS videoteka CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE videoteka;

-- Tablica korisnika
CREATE TABLE IF NOT EXISTS korisnici (
    id INT AUTO_INCREMENT PRIMARY KEY,
    korisnicko_ime VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    lozinka VARCHAR(255) NOT NULL,
    uloga ENUM('korisnik', 'administrator') DEFAULT 'korisnik',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tablica filmova
CREATE TABLE IF NOT EXISTS filmovi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    naslov VARCHAR(255) NOT NULL,
    zanr VARCHAR(150) NOT NULL,
    godina INT NOT NULL,
    trajanje_min INT NOT NULL,
    ocjena DECIMAL(3,1) NOT NULL,
    redatelj VARCHAR(150),
    zemlja VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tablica željenih filmova (osobna videoteka)
CREATE TABLE IF NOT EXISTS zeljeni_filmovi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_korisnika INT NOT NULL,
    id_filma INT NOT NULL,
    dodano_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_korisnika) REFERENCES korisnici(id) ON DELETE CASCADE, -- cascade ako se obrise korisnik brise se i njegovi zeleni filmovi
    FOREIGN KEY (id_filma) REFERENCES filmovi(id) ON DELETE CASCADE,
    UNIQUE KEY jedinstven_unos (id_korisnika, id_filma) -- isti korisnik ne može dvaput dodati isti film
);

-- Početni podaci (filmovi iz movies.csv)
INSERT INTO filmovi (naslov, zanr, godina, trajanje_min, ocjena, redatelj, zemlja) VALUES
('The Shawshank Redemption', 'Drama', 1994, 142, 9.3, 'Frank Darabont', 'USA'),
('The Godfather', 'Crime, Drama', 1972, 175, 9.2, 'Francis Ford Coppola', 'USA'),
('The Dark Knight', 'Action, Crime', 2008, 152, 9.0, 'Christopher Nolan', 'UK'),
('Schindler''s List', 'Biography, Drama', 1993, 195, 9.0, 'Steven Spielberg', 'USA'),
('12 Angry Men', 'Crime, Drama', 1957, 96, 9.0, 'Sidney Lumet', 'USA'),
('Pulp Fiction', 'Crime, Drama', 1994, 154, 8.9, 'Quentin Tarantino', 'USA'),
('The Lord of the Rings: The Return of the King', 'Action, Adventure', 2003, 201, 9.0, 'Peter Jackson', 'NZ'),
('Il Buono, il Brutto, il Cattivo', 'Western', 1966, 161, 8.8, 'Sergio Leone', 'Italy'),
('Fight Club', 'Drama', 1999, 139, 8.8, 'David Fincher', 'USA'),
('Inception', 'Action, Adventure', 2010, 148, 8.8, 'Christopher Nolan', 'USA'),
('The Matrix', 'Action, Sci-Fi', 1999, 136, 8.7, 'Lana Wachowski', 'USA'),
('Goodfellas', 'Biography, Crime', 1990, 145, 8.7, 'Martin Scorsese', 'USA'),
('One Flew Over the Cuckoo''s Nest', 'Drama', 1975, 133, 8.7, 'Milos Forman', 'USA'),
('Seven Samurai', 'Action, Drama', 1954, 207, 8.6, 'Akira Kurosawa', 'Japan'),
('Se7en', 'Crime, Drama', 1995, 127, 8.6, 'David Fincher', 'USA'),
('The Silence of the Lambs', 'Crime, Drama', 1991, 118, 8.6, 'Jonathan Demme', 'USA'),
('City of God', 'Crime, Drama', 2002, 130, 8.6, 'Fernando Meirelles', 'Brazil'),
('Life Is Beautiful', 'Comedy, Drama', 1997, 116, 8.6, 'Roberto Benigni', 'Italy'),
('Interstellar', 'Adventure, Drama', 2014, 169, 8.7, 'Christopher Nolan', 'USA'),
('Saving Private Ryan', 'Drama, War', 1998, 169, 8.6, 'Steven Spielberg', 'USA'),
('Parasite', 'Drama, Thriller', 2019, 132, 8.5, 'Bong Joon Ho', 'South Korea'),
('The Green Mile', 'Crime, Drama', 1999, 189, 8.6, 'Frank Darabont', 'USA'),
('Star Wars: Episode IV - A New Hope', 'Action, Adventure', 1977, 121, 8.6, 'George Lucas', 'USA'),
('Terminator 2: Judgment Day', 'Action, Sci-Fi', 1991, 137, 8.6, 'James Cameron', 'USA'),
('Back to the Future', 'Adventure, Comedy', 1985, 116, 8.5, 'Robert Zemeckis', 'USA'),
('The Pianist', 'Biography, Drama', 2002, 150, 8.5, 'Roman Polanski', 'France'),
('Psycho', 'Horror, Mystery', 1960, 109, 8.5, 'Alfred Hitchcock', 'USA'),
('Gladiator', 'Action, Adventure', 2000, 155, 8.5, 'Ridley Scott', 'USA'),
('The Lion King', 'Animation, Adventure', 1994, 88, 8.5, 'Roger Allers', 'USA'),
('The Departed', 'Crime, Drama', 2006, 151, 8.5, 'Martin Scorsese', 'USA');

-- Admin korisnik (korisnicko ime: admin, lozinka: password)
INSERT INTO korisnici (korisnicko_ime, email, lozinka, uloga) VALUES
('admin', 'admin@videoteka.hr', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'administrator');


-- ZADATAK 2: Ocjenjivanje fotografija

-- Tablica slika (puni se automatski iz foldera public/images preko galerija.php)
CREATE TABLE IF NOT EXISTS slike (
    id INT AUTO_INCREMENT PRIMARY KEY,
    naziv_datoteke VARCHAR(255) NOT NULL UNIQUE, -- npr. 01-movie.jpg; UNIQUE da se ista slika ne ubaci dvaput
    putanja VARCHAR(255) NOT NULL, -- relativna putanja do slike
    opis VARCHAR(255) DEFAULT NULL
);

-- Tablica ocjena (1-5 zvjezdica)
CREATE TABLE IF NOT EXISTS ocjene (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_korisnik INT NOT NULL,
    id_slika INT NOT NULL,
    ocjena TINYINT NOT NULL, -- 1 do 5
    vrijeme_ocjene TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_korisnik) REFERENCES korisnici(id) ON DELETE CASCADE,
    FOREIGN KEY (id_slika) REFERENCES slike(id) ON DELETE CASCADE,
    UNIQUE KEY jedan_glas (id_korisnik, id_slika) -- jedan korisnik = jedna ocjena po slici (ponovna ocjena ažurira)
);
