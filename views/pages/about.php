<?php
/**
 * About Us Page — Amethystclothing.lk
 * Sharp Minimalist Luxury / High-Contrast Streetwear
 */
$pageTitle = 'About Us — Amethystclothing.lk | Just own Your Style!';
$whatsappClean = '94770000000';
?>
<section class="section" style="padding-top: 50px;">
    <div class="container">
        <!-- Breadcrumbs -->
        <nav class="breadcrumb-nav">
            <a href="<?= base_url() ?>">HOME</a>
            <span>/</span>
            <strong>ABOUT US</strong>
        </nav>

        <!-- Brand Manifesto Header -->
        <div style="border-bottom: 1px solid var(--border-crisp); padding-bottom: 32px; margin-bottom: 48px;">
            <span style="font-family: var(--font-mono); font-size: 11px; letter-spacing: 0.16em; text-transform: uppercase; color: var(--brand-deep);">
                MANIFESTO // ARCHITECTURE OF STYLE
            </span>
            <h1 style="font-family: var(--font-display); font-size: clamp(32px, 5vw, 48px); margin: 8px 0 16px; text-transform: uppercase; letter-spacing: -0.02em;">
                JUST OWN YOUR STYLE!
            </h1>
            <p style="font-size: 16px; color: var(--text-secondary); max-width: 720px; line-height: 1.6;">
                Amethyst is a high-end streetwear and bespoke apparel imprint rooted in Sri Lanka. We reject generic mass-market fast fashion in favor of heavyweight combed cotton, architectural silhouettes, and precision high-density screen printing.
            </p>
        </div>

        <!-- Brand Pillars Grid -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 24px; margin-bottom: 60px;">
            <div style="background: #FFFFFF; border: 1px solid var(--border-crisp); padding: 32px;">
                <span style="font-family: var(--font-mono); font-size: 11px; color: var(--brand-deep); font-weight: 700;">PILLAR 01 // FABRIC</span>
                <h3 style="font-family: var(--font-display); font-size: 18px; margin: 12px 0 8px; text-transform: uppercase;">220–240 GSM HEAVYWEIGHT COTTON</h3>
                <p style="font-size: 13px; color: var(--text-secondary); line-height: 1.6; margin: 0;">
                    Export-grade, pre-shrunk, 100% combed cotton. Built to drape cleanly with an intentional drop-shoulder silhouette that maintains its structure after dozens of washes.
                </p>
            </div>

            <div style="background: #FFFFFF; border: 1px solid var(--border-crisp); padding: 32px;">
                <span style="font-family: var(--font-mono); font-size: 11px; color: var(--brand-deep); font-weight: 700;">PILLAR 02 // PRINTING</span>
                <h3 style="font-family: var(--font-display); font-size: 18px; margin: 12px 0 8px; text-transform: uppercase;">PRECISION T-SHIRT PRINTING</h3>
                <p style="font-size: 13px; color: var(--text-secondary); line-height: 1.6; margin: 0;">
                    We specialize in high-density plastisol and soft-feel waterbase printing. Sharp line fidelity, vivid color saturation, and anti-cracking longevity on both drops and custom runs.
                </p>
            </div>

            <div style="background: #FFFFFF; border: 1px solid var(--border-crisp); padding: 32px;">
                <span style="font-family: var(--font-mono); font-size: 11px; color: var(--brand-deep); font-weight: 700;">PILLAR 03 // SERVICE</span>
                <h3 style="font-family: var(--font-display); font-size: 18px; margin: 12px 0 8px; text-transform: uppercase;">14-DAY DOORSTEP EXCHANGE</h3>
                <p style="font-size: 13px; color: var(--text-secondary); line-height: 1.6; margin: 0;">
                    We eliminate online sizing anxiety. If a fit doesn't sit exactly right, our islandwide doorstep size exchange service handles the replacement smoothly without hassle.
                </p>
            </div>
        </div>

        <!-- Official Channels & Community Hub -->
        <div style="background: #0B0910; color: #FFFFFF; padding: 48px 36px; border: 1px solid #231E2C;">
            <div style="max-width: 600px; margin-bottom: 32px;">
                <span style="font-family: var(--font-mono); font-size: 11px; color: var(--brand-light); letter-spacing: 0.14em; text-transform: uppercase;">
                    COMMUNITY // CONNECT WITH AMETHYST
                </span>
                <h2 style="font-family: var(--font-display); font-size: 26px; text-transform: uppercase; margin: 8px 0 12px;">
                    JOIN THE INNER CIRCLE
                </h2>
                <p style="font-size: 13px; color: #A49EB2; line-height: 1.6; margin: 0;">
                    Access exclusive VIP drop announcements, custom apparel pre-orders, and behind-the-scenes print shop production logs across our verified community channels.
                </p>
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px;">
                <!-- WhatsApp Hotline -->
                <a href="https://wa.me/<?= e($whatsappClean) ?>?text=<?= urlencode('Hi Amethyst! Connecting from the website.') ?>" target="_blank" rel="noopener" 
                   style="background: #15121E; border: 1px solid #2C2638; padding: 18px; text-decoration: none; color: #FFFFFF; display: block; transition: border-color 0.15s;">
                    <div style="font-family: var(--font-mono); font-size: 10px; color: #10B981; margin-bottom: 4px;">WHATSAPP HOTLINE</div>
                    <div style="font-weight: 700; font-size: 14px;">+94 77 000 0000 &rarr;</div>
                </a>

                <!-- WhatsApp Community Group -->
                <a href="https://chat.whatsapp.com/invite/amethystclothingvip" target="_blank" rel="noopener" 
                   style="background: #15121E; border: 1px solid #2C2638; padding: 18px; text-decoration: none; color: #FFFFFF; display: block; transition: border-color 0.15s;">
                    <div style="font-family: var(--font-mono); font-size: 10px; color: var(--brand-light); margin-bottom: 4px;">VIP COMMUNITY GROUP</div>
                    <div style="font-weight: 700; font-size: 14px;">Join WhatsApp Group &rarr;</div>
                </a>

                <!-- Instagram -->
                <a href="https://instagram.com/amethystclothing.lk" target="_blank" rel="noopener" 
                   style="background: #15121E; border: 1px solid #2C2638; padding: 18px; text-decoration: none; color: #FFFFFF; display: block; transition: border-color 0.15s;">
                    <div style="font-family: var(--font-mono); font-size: 10px; color: #EC4899; margin-bottom: 4px;">INSTAGRAM</div>
                    <div style="font-weight: 700; font-size: 14px;">@amethystclothing.lk &rarr;</div>
                </a>

                <!-- TikTok -->
                <a href="https://tiktok.com/@amethystclothing.lk" target="_blank" rel="noopener" 
                   style="background: #15121E; border: 1px solid #2C2638; padding: 18px; text-decoration: none; color: #FFFFFF; display: block; transition: border-color 0.15s;">
                    <div style="font-family: var(--font-mono); font-size: 10px; color: #38BDF8; margin-bottom: 4px;">TIKTOK</div>
                    <div style="font-weight: 700; font-size: 14px;">@amethystclothing.lk &rarr;</div>
                </a>

                <!-- Facebook -->
                <a href="https://facebook.com/amethystclothing.lk" target="_blank" rel="noopener" 
                   style="background: #15121E; border: 1px solid #2C2638; padding: 18px; text-decoration: none; color: #FFFFFF; display: block; transition: border-color 0.15s;">
                    <div style="font-family: var(--font-mono); font-size: 10px; color: #60A5FA; margin-bottom: 4px;">FACEBOOK</div>
                    <div style="font-weight: 700; font-size: 14px;">Amethyst Clothing &rarr;</div>
                </a>

                <!-- YouTube -->
                <a href="https://youtube.com/@amethystclothinglk" target="_blank" rel="noopener" 
                   style="background: #15121E; border: 1px solid #2C2638; padding: 18px; text-decoration: none; color: #FFFFFF; display: block; transition: border-color 0.15s;">
                    <div style="font-family: var(--font-mono); font-size: 10px; color: #EF4444; margin-bottom: 4px;">YOUTUBE</div>
                    <div style="font-weight: 700; font-size: 14px;">Amethyst Official &rarr;</div>
                </a>

                <!-- Discord -->
                <a href="https://discord.gg/amethystclothing" target="_blank" rel="noopener" 
                   style="background: #15121E; border: 1px solid #2C2638; padding: 18px; text-decoration: none; color: #FFFFFF; display: block; transition: border-color 0.15s;">
                    <div style="font-family: var(--font-mono); font-size: 10px; color: #818CF8; margin-bottom: 4px;">DISCORD SERVER</div>
                    <div style="font-weight: 700; font-size: 14px;">Streetwear Collective &rarr;</div>
                </a>
            </div>
        </div>
    </div>
</section>
