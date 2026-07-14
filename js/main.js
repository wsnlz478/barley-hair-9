/* ========================================
   Barley Hair Transplant - Main JavaScript
   ======================================== */

        // Helper function to shuffle array
        function shuffleArray(array) {
            for (let i = array.length - 1; i > 0; i--) {
                const j = Math.floor(Math.random() * (i + 1));
                [array[i], array[j]] = [array[j], array[i]];
            }
            return array;
        }

        // Hero images
        const heroImages = [];
        for (let i = 1; i <= 23; i++) {
            heroImages.push(`Banner/hero-${i}.jpg`);
        }
        const heroTexts = [
            { title: "Hair Transplant in China", subtitle: "Experience world-class hair restoration - China's pioneer and leader in microneedle hair transplant technology since 2006." },
            { title: "Natural Results That Last", subtitle: "With 30+ direct-operated clinics, 10+ patented technologies, and Sullivan-certified excellence, we deliver remarkable results for patients from around the globe." },
            { title: "Transform Your Look Today", subtitle: "Free consultation available in English! Contact us now to start your hair restoration journey." },
            { title: "Microneedle Technology Leader", subtitle: "Our revolutionary implant pen ensures 360° rotation for natural direction and minimal trauma." },
            { title: "18+ Years of Excellence", subtitle: "Trust the pioneer and leader of microneedle hair transplant in China." }
        ];

        // Before & After images
        const beforeAfterImages = [];
        for (let i = 1; i <= 5; i++) beforeAfterImages.push({ path: `Beard/Beard-${i}.jpg`, type: 'Beard' });
        for (let i = 1; i <= 2; i++) beforeAfterImages.push({ path: `Eyebrow/Eyebrow-${i}.jpg`, type: 'Eyebrow' });
        for (let i = 1; i <= 26; i++) beforeAfterImages.push({ path: `manBald/manBald-${i}.jpg`, type: 'Male Baldness' });
        for (let i = 1; i <= 35; i++) beforeAfterImages.push({ path: `manHairline/manHairline-${i}.jpg`, type: 'Male Hairline' });
        for (let i = 1; i <= 33; i++) beforeAfterImages.push({ path: `women/women-${i}.jpg`, type: 'Female Hair' });

        const beforeAfterCaptions = [
            { title: "Amazing Transformation", desc: "Before and after comparison" },
            { title: "Natural Results", desc: "Real patient transformation" },
            { title: "Transformation Complete", desc: "Remarkable results" },
            { title: "Dramatic Improvement", desc: "Before and after photos" },
            { title: "Natural Looking", desc: "Professional hair restoration" },
            { title: "Perfect Density", desc: "Outstanding results" },
            { title: "Remarkable Transformation", desc: "Before and after comparison" },
            { title: "New Look, New Life", desc: "Patient success story" },
            { title: "Remarkable Results", desc: "Real patient photos" },
            { title: "Excellent Results", desc: "Before and after comparison" }
        ];



        // Doctor Patient Photos
        const doctorPatientPhotos = [];
        for (let i = 1; i <= 76; i++) {
            doctorPatientPhotos.push(`Photos/Photos-${i}.jpg`);
        }
        const photoCaptions = [
            { title: "Happy Patient", desc: "Posing with our medical team after successful procedure" },
            { title: "Grateful Patient", desc: "Celebrating fantastic results with our doctors" },
            { title: "Success Story", desc: "Another satisfied patient shares their joy" },
            { title: "Smile Restored", desc: "Patient with our specialist before discharge" },
            { title: "Natural Look Restored", desc: "Happy patient with Dr. Chen post-treatment" },
            { title: "Transformation Success", desc: "Excited patient showing their new look" },
            { title: "Confidence Regained", desc: "Patient delighted with their transformation" },
            { title: "New Journey Begins", desc: "Patient starting fresh with amazing results" }
        ];

        // Hospital Environment
        const hospitalImages = [];
        for (let i = 1; i <= 73; i++) {
            hospitalImages.push(`Hospital/Hospital-${i}.jpg`);
        }
        const hospitalCaptions = [
            { title: "Modern Clinic", desc: "Our state-of-the-art facility" },
            { title: "Comfortable Waiting Area", desc: "Relax in our premium lounge" },
            { title: "Operating Room", desc: "Sterile and advanced" },
            { title: "Consultation Room", desc: "Private and professional" },
            { title: "Recovery Area", desc: "Comfortable post-op space" },
            { title: "Reception", desc: "Welcoming entrance" },
            { title: "Treatment Room", desc: "Well-equipped and clean" },
            { title: "Doctor's Office", desc: "Professional workspace" },
            { title: "Clinic Interior", desc: "Modern and welcoming" },
            { title: "Modern Facilities", desc: "Designed for your comfort" }
        ];

        // Testimonials
        const testimonialsData = [
            { name: "James Wilson", country: "USA", text: "Amazing results! The team at Barley was incredibly professional and the results exceeded all my expectations. Highly recommend to anyone considering hair transplant.", initials: "JW" },
            { name: "Sarah Chen", country: "Canada", text: "From start to finish, the experience was wonderful. The doctors were so knowledgeable and the staff spoke excellent English. My hair looks completely natural!", initials: "SC" },
            { name: "Michael Brown", country: "UK", text: "I traveled from London to Shanghai for my procedure and it was worth every mile. The microneedle technology is incredible and recovery was much faster than I expected.", initials: "MB" },
            { name: "David Kim", country: "South Korea", text: "Professional, friendly, and the results speak for themselves. I'm so happy with my decision to choose Barley. The aftercare was also fantastic!", initials: "DK" },
            { name: "Emma Johnson", country: "Australia", text: "Best decision ever! The team made me feel so comfortable throughout the entire process. My hairline looks amazing and I feel so much more confident.", initials: "EJ" },
            { name: "Robert Taylor", country: "Germany", text: "Exceptional service from beginning to end. The doctors are true experts in their field. I couldn't be happier with the results!", initials: "RT" }
        ];

        // FAQ Data
        const faqData = [
            { q: "How long does a hair transplant procedure take?", a: "The duration depends on the number of grafts needed. Typically, a procedure takes 4-8 hours. Our team will provide you with a precise timeline during your consultation." },
            { q: "When will I see results?", a: "You'll start seeing new hair growth at 3-4 months. Maximum results are typically visible at 12-18 months post-procedure as the hair continues to thicken and mature." },
            { q: "Is the procedure painful?", a: "We use local anesthesia to ensure you're comfortable throughout the procedure. Most patients report minimal discomfort and can watch movies or listen to music during treatment." },
            { q: "How soon can I return to work?", a: "Most patients can return to desk work within 2-3 days. For more physical jobs, we recommend waiting 7-10 days. Our team will provide specific aftercare instructions." },
            { q: "Are the results permanent?", a: "Yes! The transplanted hair is taken from the donor area (back of the head) which is genetically resistant to DHT and hair loss. These hairs will continue to grow naturally for a lifetime." },
            { q: "Do you offer consultations in English?", a: "Absolutely! We have English-speaking staff available for consultations and throughout your treatment journey to ensure clear communication." },
            { q: "What is microneedle hair transplant?", a: "Microneedle transplant uses a specialized implant pen that allows 360° rotation for natural hair direction. It results in smaller incisions (0.6-1.0mm), faster recovery, and higher density." },
            { q: "Can I wash my hair after the procedure?", a: "Yes! With our microneedle technology, most patients can wash their hair gently within 24 hours. We'll provide you with detailed instructions for proper care." },
            { q: "What is the cost of hair transplant?", a: "The cost varies depending on the number of grafts needed and the specific procedure. We offer competitive pricing and flexible payment options. Contact us for a personalized quote." },
            { q: "Do you have clinics in other cities?", a: "Yes! We have 30+ direct-operated clinics across major Chinese cities including Beijing, Shanghai, Shenzhen, Guangzhou, Chengdu, and many more." }
        ];

        // Initialize Hero
        let currentSlide = 0;
        const selectedHeroes = shuffleArray(heroImages).slice(0, 3);
        
        function renderHero() {
            const slidesContainer = document.getElementById('heroSlides');
            if (!slidesContainer) return;
            
            // 检查是否有静态内容 - 如果有，只替换图片src
            const existingSlides = slidesContainer.querySelectorAll('.hero-slide');
            if (existingSlides.length > 0) {
                // 只替换图片，保留文案
                existingSlides.forEach((slide, index) => {
                    const img = slide.querySelector('img');
                    if (img && selectedHeroes[index]) {
                        img.src = selectedHeroes[index];
                    }
                });
                return;
            }
            
            // 如果没有静态内容，就生成完整的hero
            slidesContainer.innerHTML = selectedHeroes.map((img, index) => {
                const text = heroTexts[index % heroTexts.length];
                return `
                    <div class="hero-slide">
                        <img src="${img}" alt="Hero ${index + 1}">
                        <div class="hero-overlay">
                            <div class="hero-content">
                                <h1>${text.title}</h1>
                                <p>${text.subtitle}</p>
                                <a href="contact.html" class="btn">Get Free Consultation</a>
                            </div>
                        </div>
                    </div>
                `;
            }).join('');
        }
        
        function goToSlide(index) {
            const heroSlides = document.getElementById('heroSlides');
            if (!heroSlides) return;
            
            const slides = heroSlides.querySelectorAll('.hero-slide');
            if (slides.length === 0) return;
            
            currentSlide = index;
            heroSlides.style.transform = `translateX(-${index * 100}%)`;
            document.querySelectorAll('.hero-dot').forEach((dot, i) => {
                dot.classList.toggle('active', i === index);
            });
        }
        
        function nextSlide() {
            const heroSlides = document.getElementById('heroSlides');
            if (!heroSlides) return;
            
            const slides = heroSlides.querySelectorAll('.hero-slide');
            if (slides.length === 0) return;
            
            currentSlide = (currentSlide + 1) % slides.length;
            goToSlide(currentSlide);
        }
        
        // Initialize Before & After
        function renderBeforeAfter() {
            const container = document.getElementById('beforeAfterGallery');
            if (!container) return;
            
            // Always regenerate on refresh, even if static content exists
            const selected = shuffleArray([...beforeAfterImages]).slice(0, 6);
            
            container.innerHTML = selected.map((item, index) => {
                const caption = beforeAfterCaptions[index % beforeAfterCaptions.length];
                return `
                    <div class="gallery-item">
                        <img src="${item.path}" alt="${item.type}">
                        <div class="gallery-overlay">
                            <h4>${caption.title}</h4>
                            <p>${caption.desc}</p>
                        </div>
                    </div>
                `;
            }).join('');
        }
        
        // Initialize Doctor Patient Photos
        function renderDoctorPatientPhotos() {
            const container = document.getElementById('doctorPatientPhotos');
            if (!container) return;
            
            // Always regenerate on refresh, even if static content exists
            const selected = shuffleArray([...doctorPatientPhotos]).slice(0, 8);
            
            container.innerHTML = selected.map((path, index) => {
                const caption = photoCaptions[index % photoCaptions.length];
                return `
                    <div class="photo-item">
                        <img src="${path}" alt="Patient Photo ${index + 1}">
                        <div class="photo-caption">
                            <h4>${caption.title}</h4>
                            <p>${caption.desc}</p>
                        </div>
                    </div>
                `;
            }).join('');
        }
        
        // Initialize Testimonials
        function renderTestimonials() {
            const container = document.getElementById('testimonials');
            if (!container) return;
            
            // Check if there are static testimonials - if yes, don't overwrite
            if (container.querySelector('.testimonial-card')) {
                return;
            }
            
            container.innerHTML = testimonialsData.map(t => `
                <div class="testimonial-card">
                    <div class="testimonial-stars">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                    </div>
                    <p class="testimonial-text">"${t.text}"</p>
                    <div class="testimonial-author">
                        <div class="testimonial-avatar">${t.initials}</div>
                        <div class="testimonial-info">
                            <h4>${t.name}</h4>
                            <p><i class="fas fa-map-marker-alt"></i> ${t.country}</p>
                        </div>
                    </div>
                </div>
            `).join('');
        }
        
        // Initialize Hospital Gallery
        function renderHospitalGallery() {
            const container = document.getElementById('hospitalGallery');
            if (!container) return;
            
            // Always regenerate on refresh, even if static content exists
            const selected = shuffleArray([...hospitalImages]).slice(0, 6);
            
            container.innerHTML = selected.map((path, index) => {
                const caption = hospitalCaptions[index % hospitalCaptions.length];
                return `
                    <div class="hospital-item">
                        <img src="${path}" alt="Hospital ${index + 1}">
                        <div class="hospital-caption">
                            <h4>${caption.title}</h4>
                            <p>${caption.desc}</p>
                        </div>
                    </div>
                `;
            }).join('');
        }
        
        // Initialize FAQ
        function renderFAQ() {
            const container = document.getElementById('faqContainer');
            if (!container) return;
            
            // Check if there are static FAQ items - if yes, don't overwrite
            if (container.querySelector('.faq-item')) {
                return;
            }
            
            container.innerHTML = faqData.map((faq, index) => `
                <div class="faq-item ${index === 0 ? 'active' : ''}">
                    <div class="faq-question" onclick="toggleFAQElement(this)">
                        <span>${faq.q}</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        ${faq.a}
                    </div>
                </div>
            `).join('');
        }
        
        function toggleFAQ(element) {
            // 处理传入 element 的情况（faq.html 中使用）
            const faqItem = element.closest('.faq-item');
            if (faqItem) {
                faqItem.classList.toggle('active');
                return;
            }
            
            // 处理传入 index 的情况（其他页面）
            const items = document.querySelectorAll('.faq-item');
            if (!items[element]) return;
            items[element].classList.toggle('active');
        }
        
        function toggleFAQElement(element) {
            const faqItem = element.closest('.faq-item');
            if (faqItem) {
                faqItem.classList.toggle('active');
            }
        }
        
        // Copy to clipboard function - make it global
        window.copyToClipboard = function(text, label) {
            const ta = document.createElement('textarea');
            ta.value = text;
            ta.style.position = 'fixed';
            ta.style.left = '-9999px';
            ta.style.top = '0';
            ta.style.opacity = '0';
            ta.setAttribute('readonly', '');
            document.body.appendChild(ta);
            ta.select();
            ta.setSelectionRange(0, ta.value.length);
            let ok = false;
            try {
                ok = document.execCommand('copy');
            } catch (e) {}
            document.body.removeChild(ta);
            if (ok) {
                window.showCopySuccess(label);
            } else {
                // Try modern clipboard API as fallback
                if (navigator.clipboard && navigator.clipboard.writeText) {
                    navigator.clipboard.writeText(text).then(function() {
                        window.showCopySuccess(label);
                    }).catch(function() {
                        alert('Please copy manually: ' + text);
                    });
                } else {
                    alert('Please copy manually: ' + text);
                }
            }
        }
        
        window.showCopySuccess = function(label) {
            let notification = document.getElementById('copy-notification');

            if (!notification) {
                notification = document.createElement('div');
                notification.id = 'copy-notification';
                notification.className = 'copy-notification';
                notification.innerHTML = `<i class="fas fa-check-circle"></i> <span id="copy-text"></span>`;
                document.body.appendChild(notification);
            }

            const copyText = document.getElementById('copy-text');
            if (copyText) {
                copyText.textContent = `${label} copied!`;
            }

            notification.classList.add('show');

            setTimeout(() => {
                notification.classList.remove('show');
            }, 2500);
        }
        
        // Initialize FAQ Category Tabs
        function initFAQCategories() {
            const tabs = document.querySelectorAll('.category-tab');
            const categories = document.querySelectorAll('.faq-category');
            
            if (tabs.length === 0 || categories.length === 0) return;
            
            // 初始显示所有分类
            categories.forEach(cat => cat.classList.add('active'));
            
            tabs.forEach(tab => {
                tab.addEventListener('click', function() {
                    const categoryId = this.getAttribute('data-category');
                    
                    // Update active tab
                    tabs.forEach(t => t.classList.remove('active'));
                    this.classList.add('active');
                    
                    // Update active category
                    if (categoryId === 'all') {
                        // 显示所有分类
                        categories.forEach(cat => cat.classList.add('active'));
                    } else {
                        // 只显示选中的分类
                        categories.forEach(cat => cat.classList.remove('active'));
                        const targetCategory = document.getElementById(categoryId);
                        if (targetCategory) {
                            targetCategory.classList.add('active');
                        }
                    }
                });
            });
        }

        // Hamburger Menu Toggle
        function toggleHamburger() {
            const hamburger = document.querySelector('.hamburger');
            const nav = document.querySelector('nav');
            
            if (hamburger && nav) {
                hamburger.classList.toggle('active');
                nav.classList.toggle('active');
                
                // Prevent scrolling when menu is open
                document.body.style.overflow = nav.classList.contains('active') ? 'hidden' : '';
            }
        }
        
        // Back to Top Button
        function initBackToTop() {
            const backToTopBtn = document.createElement('button');
            backToTopBtn.className = 'back-to-top';
            backToTopBtn.innerHTML = '<i class="fas fa-arrow-up"></i>';
            document.body.appendChild(backToTopBtn);
            
            window.addEventListener('scroll', function() {
                if (window.scrollY > 300) {
                    backToTopBtn.classList.add('show');
                } else {
                    backToTopBtn.classList.remove('show');
                }
            });
            
            backToTopBtn.addEventListener('click', function() {
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
            });
        }
        
        // Initialize all
        document.addEventListener('DOMContentLoaded', function() {
            renderHero();
            renderBeforeAfter();
            renderDoctorPatientPhotos();
            renderTestimonials();
            renderHospitalGallery();
            renderFAQ();
            initFAQCategories();
            initBackToTop();
            
            // Add hamburger click listener
            const hamburger = document.querySelector('.hamburger');
            if (hamburger) {
                hamburger.addEventListener('click', toggleHamburger);
            }
            
            // Close menu when clicking a link
            document.querySelectorAll('nav a').forEach(link => {
                link.addEventListener('click', function() {
                    const hamburger = document.querySelector('.hamburger');
                    const nav = document.querySelector('nav');
                    if (hamburger && nav) {
                        hamburger.classList.remove('active');
                        nav.classList.remove('active');
                        document.body.style.overflow = '';
                    }
                });
            });
        });
        
        // Auto-rotate hero (temporarily disabled)
        // setInterval(nextSlide, 5000);
    