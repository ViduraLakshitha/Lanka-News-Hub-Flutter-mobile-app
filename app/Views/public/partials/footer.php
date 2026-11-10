<footer class="master-footer">
    <div class="container">
        <div class="row g-4">
            <!-- Brand & Bio -->
            <div class="col-lg-4 col-md-6">
                <div class="footer-logo mb-3">
                    LANKA<span>NEWS HUB</span>
                </div>
                <p class="small text-secondary mb-4" style="line-height: 1.8;">
                    Sri Lanka's trusted digital news platform delivering unbiased, accurate, and comprehensive coverage across politics, economy, sports, world affairs, and entertainment.
                </p>
                <div class="d-flex gap-2">
                    <a href="#" class="share-btn share-fb" title="Facebook"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" class="share-btn share-tw" title="Twitter / X"><i class="fab fa-x-twitter"></i></a>
                    <a href="#" class="share-btn" style="background:#ff0000;" title="YouTube"><i class="fab fa-youtube"></i></a>
                    <a href="#" class="share-btn share-wa" title="WhatsApp"><i class="fab fa-whatsapp"></i></a>
                    <a href="#" class="share-btn share-tg" title="Telegram"><i class="fab fa-telegram-plane"></i></a>
                </div>
            </div>
            
            <!-- Category Quick Links -->
            <div class="col-lg-2 col-md-6">
                <div class="footer-heading">Categories</div>
                <ul class="footer-link-list">
                    <?php if (!empty($categories)): ?>
                        <?php foreach (array_slice($categories, 0, 6) as $cat): ?>
                            <li>
                                <a href="<?= APP_URL ?>/category/<?= $cat['slug'] ?>">
                                    <i class="fas fa-angle-right text-danger small"></i> <?= htmlspecialchars(!empty($cat['name_en']) ? $cat['name_en'] : $cat['name_si']) ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </ul>
            </div>

            <!-- Corporate & Legal Links -->
            <div class="col-lg-2 col-md-6">
                <div class="footer-heading">Quick Links</div>
                <ul class="footer-link-list">
                    <li><a href="<?= APP_URL ?>"><i class="fas fa-angle-right text-danger small"></i> Home</a></li>
                    <li><a href="#"><i class="fas fa-angle-right text-danger small"></i> About Us</a></li>
                    <li><a href="#"><i class="fas fa-angle-right text-danger small"></i> Contact Editorial</a></li>
                    <li><a href="#"><i class="fas fa-angle-right text-danger small"></i> Privacy Policy</a></li>
                    <li><a href="#"><i class="fas fa-angle-right text-danger small"></i> Terms & Conditions</a></li>
                    <li><a href="<?= APP_URL ?>/admin/login"><i class="fas fa-angle-right text-danger small"></i> Newsroom Login</a></li>
                </ul>
            </div>
            
            <!-- Newsletter & Editorial Contact -->
            <div class="col-lg-4 col-md-6">
                <div class="footer-newsletter-card">
                    <div class="footer-heading text-white">Daily Newsletter</div>
                    <p class="small text-secondary mb-3">
                        Subscribe to get top headlines and breaking updates delivered straight to your inbox every morning.
                    </p>
                    <form onsubmit="event.preventDefault(); alert('Thank you for subscribing to Lanka News Hub!');">
                        <div class="input-group mb-2">
                            <input type="email" class="form-control rounded-0 bg-dark text-white border-secondary small" placeholder="Enter your email address" required>
                            <button class="btn btn-danger rounded-0 px-3 fw-bold" type="submit">Subscribe</button>
                        </div>
                        <small class="text-muted" style="font-size: 0.72rem;">*We respect your privacy and never share your data.</small>
                    </form>
                </div>
            </div>
        </div>
        
        <hr class="my-4 border-secondary opacity-25">
        
        <div class="row align-items-center">
            <div class="col-md-6 text-center text-md-start small text-secondary">
                &copy; <?= date('Y') ?> <strong>Lanka News Hub</strong>. All rights reserved.
            </div>
            <div class="col-md-6 text-center text-md-end small text-secondary mt-2 mt-md-0">
                Developed with <i class="fas fa-heart text-danger"></i> for Modern Digital Journalism.
            </div>
        </div>
    </div>
</footer>

<!-- Bootstrap 5.3 JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
