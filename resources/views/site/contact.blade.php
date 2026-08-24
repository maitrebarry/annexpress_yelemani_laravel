<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title>À propos & Contact - TransGest</title>
    <link rel="icon" href="{{ asset('assets_site/img/favicon.svg') }}">
    <link href="{{ asset('assets_site/css/inter.css') }}" rel="stylesheet">
    <link href="{{ asset('assets_site/css/all.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets_site/css/aos.css') }}" rel="stylesheet">
    <link href="{{ asset('assets_site/css/site-common.css') }}" rel="stylesheet">
    <style>
        /* ========== SECTION À PROPOS ========== */
        .about-section {
            padding: 60px 0;
        }
        .about-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 50px;
            align-items: center;
        }
        .about-content h2 {
            font-size: 2rem;
            margin-bottom: 20px;
            color: var(--primary);
        }
        .about-content p {
            color: var(--gray);
            line-height: 1.6;
            margin-bottom: 20px;
        }
        .about-stats {
            display: flex;
            gap: 30px;
            margin-top: 30px;
        }
        .stat {
            text-align: center;
        }
        .stat-number {
            font-size: 2rem;
            font-weight: 800;
            color: var(--secondary);
        }
        .stat-label {
            font-size: 0.8rem;
            color: var(--gray);
        }
        .about-image img {
            width: 100%;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-lg);
        }

        /* ========== NOS VALEURS ========== */
        .values-section {
            background: #f8fafc;
            padding: 60px 0;
        }
        .section-title {
            text-align: center;
            font-size: 2rem;
            margin-bottom: 50px;
            color: var(--primary);
        }
        .values-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 30px;
        }
        .value-card {
            background: white;
            padding: 30px;
            border-radius: var(--radius-lg);
            text-align: center;
            box-shadow: var(--shadow);
            transition: all 0.3s;
        }
        .value-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
        }
        .value-icon {
            width: 70px;
            height: 70px;
            background: rgba(230, 126, 34, 0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 1.8rem;
            color: var(--secondary);
        }
        .value-card h3 {
            margin-bottom: 15px;
        }
        .value-card p {
            color: var(--gray);
            font-size: 0.9rem;
            line-height: 1.5;
        }

        /* ========== CONTACT SECTION ========== */
        .contact-section {
            padding: 60px 0;
        }
        .contact-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 50px;
        }
        .contact-info {
            background: white;
            padding: 30px;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow);
        }
        .contact-info h3 {
            font-size: 1.5rem;
            margin-bottom: 20px;
            color: var(--primary);
        }
        .contact-details {
            margin: 30px 0;
        }
        .contact-item {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 20px;
        }
        .contact-icon {
            width: 45px;
            height: 45px;
            background: rgba(230, 126, 34, 0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--secondary);
            font-size: 1.2rem;
        }
        .contact-text h4 {
            font-size: 0.9rem;
            margin-bottom: 4px;
        }
        .contact-text p {
            color: var(--gray);
            font-size: 0.85rem;
        }
        .social-links {
            display: flex;
            gap: 15px;
            margin-top: 30px;
        }
        .social-link {
            width: 40px;
            height: 40px;
            background: var(--gray-light);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary);
            transition: all 0.3s;
        }
        .social-link:hover {
            background: var(--secondary);
            color: white;
        }
        .contact-form {
            background: white;
            padding: 30px;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow);
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            font-weight: 600;
            font-size: 0.85rem;
            margin-bottom: 8px;
            color: var(--dark);
        }
        .form-control {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e2e8f0;
            border-radius: var(--radius);
            font-size: 0.9rem;
            transition: all 0.3s;
        }
        .form-control:focus {
            outline: none;
            border-color: var(--secondary);
        }
        textarea.form-control {
            resize: vertical;
            min-height: 100px;
        }
        .btn-submit {
            background: var(--secondary);
            color: white;
            border: none;
            padding: 14px 30px;
            border-radius: var(--radius);
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            width: 100%;
        }
        .btn-submit:hover {
            background: var(--secondary-dark);
            transform: translateY(-2px);
        }

        /* ========== MAP SECTION ========== */
        .map-section {
            padding: 0 0 60px;
        }
        .map-container {
            border-radius: var(--radius-lg);
            overflow: hidden;
            box-shadow: var(--shadow-lg);
        }
        .map-container iframe {
            width: 100%;
            height: 400px;
            border: 0;
        }

        /* ========== RESPONSIVE ========== */
        @media (max-width: 992px) {
            .about-grid, .contact-grid, .values-grid {
                grid-template-columns: 1fr;
            }
        }
        @media (max-width: 768px) {
            .about-stats {
                justify-content: center;
            }
        }
    </style>
</head>
<body>

@include('site.partials.nav', ['compagnie' => $compagnie])

<!-- PAGE HEADER -->
<section class="page-header">
    <div class="container">
        <h1 data-aos="fade-up">À propos de TransGest</h1>
        <p data-aos="fade-up" data-aos-delay="100">Découvrez qui nous sommes et comment nous révolutionnons le transport au Mali</p>
    </div>
</section>

<!-- SECTION À PROPOS -->
<section class="about-section">
    <div class="container">
        <div class="about-grid">
            <div class="about-content" data-aos="fade-right">
                <h2>Votre partenaire de voyage au Mali</h2>
                <p>{{ $compagnie->nom_compagnie }} {{ $compagnie->slogant ? '— '.$compagnie->slogant : '' }} vous accompagne dans vos déplacements et l'envoi de vos colis au Mali, avec une solution de réservation en ligne fiable, rapide et sécurisée.</p>
                <p>Confort, ponctualité et sécurité à chaque trajet. Réservez en quelques clics et suivez vos colis en temps réel.</p>
                <div class="about-stats">
                    <div class="stat">
                        <div class="stat-number">{{ $stats['destinations'] }}</div>
                        <div class="stat-label">Destinations</div>
                    </div>
                    <div class="stat">
                        <div class="stat-number">{{ $stats['trajets'] }}</div>
                        <div class="stat-label">Trajets</div>
                    </div>
                    <div class="stat">
                        <div class="stat-number">{{ $stats['clients'] }}</div>
                        <div class="stat-label">Clients</div>
                    </div>
                </div>
            </div>
            <div class="about-image" data-aos="fade-left">
                <img src="{{ asset('assets_site/img/about/about-1.jpg') }}" alt="Équipe TransGest">
            </div>
        </div>
    </div>
</section>

<!-- NOS VALEURS -->
<section class="values-section">
    <div class="container">
        <h2 class="section-title" data-aos="fade-up">Nos valeurs</h2>
        <div class="values-grid">
            <div class="value-card" data-aos="fade-up" data-aos-delay="100">
                <div class="value-icon"><i class="fas fa-handshake"></i></div>
                <h3>Confiabilité</h3>
                <p>Des partenaires de confiance et un service client disponible 24h/24 pour vous accompagner.</p>
            </div>
            <div class="value-card" data-aos="fade-up" data-aos-delay="200">
                <div class="value-icon"><i class="fas fa-bolt"></i></div>
                <h3>Rapidité</h3>
                <p>Réservation instantanée, suivi en temps réel et paiement sécurisé en quelques secondes.</p>
            </div>
            <div class="value-card" data-aos="fade-up" data-aos-delay="300">
                <div class="value-icon"><i class="fas fa-smile"></i></div>
                <h3>Satisfaction</h3>
                <p>Des milliers de voyageurs satisfaits grâce à notre qualité de service et notre écoute.</p>
            </div>
        </div>
    </div>
</section>

<!-- CONTACT SECTION -->
<section class="contact-section">
    <div class="container">
        <div class="contact-grid">
            <div class="contact-info" data-aos="fade-right">
                <h3>Contactez-nous</h3>
                <p>Notre équipe est à votre disposition pour toute question ou assistance.</p>
                <div class="contact-details">
                    <div class="contact-item">
                        <div class="contact-icon"><i class="fas fa-map-marker-alt"></i></div>
                        <div class="contact-text">
                            <h4>Adresse</h4>
                            <p>Pelegana, Segou, Mali</p>
                        </div>
                    </div>
                    <div class="contact-item">
                        <div class="contact-icon"><i class="fas fa-phone-alt"></i></div>
                        <div class="contact-text">
                            <h4>Téléphone</h4>
                            <p>+223 90 25 94 38</p>
                        </div>
                    </div>
                    <div class="contact-item">
                        <div class="contact-icon"><i class="fas fa-envelope"></i></div>
                        <div class="contact-text">
                            <h4>Email</h4>
                            <p>transgest@gmail.com</p>
                        </div>
                    </div>
                    <div class="contact-item">
                        <div class="contact-icon"><i class="fas fa-clock"></i></div>
                        <div class="contact-text">
                            <h4>Horaires</h4>
                            <p>24/7</p>
                        </div>
                    </div>
                </div>
                <div class="social-links">
                    <a href="#" class="social-link"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" class="social-link"><i class="fab fa-twitter"></i></a>
                    <a href="#" class="social-link"><i class="fab fa-instagram"></i></a>
                    <a href="#" class="social-link"><i class="fab fa-linkedin-in"></i></a>
                </div>
            </div>
            <div class="contact-form" data-aos="fade-left">
                <h3>Envoyez-nous un message</h3>
                <form onsubmit="tgBientot(event)">
                    <div class="form-group">
                        <label>Nom complet</label>
                        <input type="text" class="form-control" placeholder="Votre nom">
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" class="form-control" placeholder="votre@email.com">
                    </div>
                    <div class="form-group">
                        <label>Téléphone</label>
                        <input type="tel" class="form-control" placeholder="77 78 88 99">
                    </div>
                    <div class="form-group">
                        <label>Message</label>
                        <textarea class="form-control" placeholder="Votre message..."></textarea>
                    </div>
                    <button type="submit" class="btn-submit">Envoyer le message <i class="fas fa-paper-plane"></i></button>
                </form>
            </div>
        </div>
    </div>
</section>

<!-- MAP SECTION -->
<section class="map-section">
    <div class="container">
        <div class="map-container" data-aos="fade-up">
            <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3868.382152195884!2d-8.002433!3d12.639465!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0xe5c6f8b8b8b8b8b%3A0x8b8b8b8b8b8b8b8b!2sBamako%2C%20Mali!5e0!3m2!1sfr!2sfr!4v1700000000000!5m2!1sfr!2sfr" allowfullscreen loading="lazy"></iframe>
        </div>
    </div>
</section>

<!-- FOOTER -->
<footer class="footer">
    <div class="container">
        <div class="footer-grid">
            <div><h4>TransGest</h4><p style="font-size: 0.85rem;">La plateforme N°1 de réservation de billets de bus et suivi de colis au Mali.</p></div>
            <div><h4>Liens rapides</h4>
                <a href="{{ route('site.home') }}">Accueil</a>
                <a href="{{ route('site.compagnies') }}">Compagnies</a>
                <a href="{{ route('site.contact') }}">Contact</a>
            </div>
            <div><h4>Support</h4><a href="#" onclick="tgBientot(event)">FAQ</a><a href="#" onclick="tgBientot(event)">Conditions générales</a><a href="#" onclick="tgBientot(event)">Politique de confidentialité</a></div>
            <div><h4>Contact</h4><a href="tel:+22390259438"><i class="fas fa-phone"></i> +223 90 25 94 38</a><a href="mailto:transgest@gmail.com"><i class="fas fa-envelope"></i> transgest@gmail.com</a><a href="#"><i class="fas fa-map-marker-alt"></i> Pelegana, Segou, Mali</a></div>
        </div>
        <div class="footer-bottom"><p>Copyright &copy; 2026 Computer Service Barry. All rights reserved.</p></div>
    </div>
</footer>

<script src="{{ asset('assets_site/js/aos.js') }}"></script>
<script>
    AOS.init({ duration: 600, once: true, offset: 50 });
</script>
</body>
</html>
