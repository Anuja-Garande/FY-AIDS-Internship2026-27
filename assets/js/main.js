/* ============================================
   AI Shopping - Main JavaScript
   ============================================ */

document.addEventListener('DOMContentLoaded', function () {

  /* -------------------------------------------
     1. Dark/Light Mode Toggle
     ------------------------------------------- */
  var saved = localStorage.getItem('theme') || 'dark';
  document.documentElement.setAttribute('data-theme', saved);

  /* -------------------------------------------
     2. Navbar Scroll Effect
     ------------------------------------------- */
  var navbar = document.querySelector('.navbar');
  if (navbar) {
    window.addEventListener('scroll', function() {
      navbar.classList.toggle('scrolled', window.scrollY > 50);
    }, { passive: true });
  }

  /* -------------------------------------------
     3. Search Functionality (AI Smart Search)
     ------------------------------------------- */
  var SearchModule = {
    input: null,
    dropdown: null,
    debounceTimer: null,
    activeIndex: -1,
    results: [],
    init: function() {
      this.input = document.querySelector('.search-bar input[name="q"]');
      this.dropdown = document.querySelector('.search-dropdown');
      if (!this.input) return;
      if (!this.dropdown) {
        this.dropdown = document.createElement('div');
        this.dropdown.className = 'search-dropdown';
        this.input.parentNode.appendChild(this.dropdown);
      }
      var self = this;
      this.input.addEventListener('input', function() { self.onInput(); });
      this.input.addEventListener('focus', function() { self.onFocus(); });
      this.input.addEventListener('keydown', function(e) { self.onKeydown(e); });
      document.addEventListener('click', function(e) {
        if (!e.target.closest('.search-bar')) self.hide();
      });
    },
    onInput: function() {
      var self = this;
      clearTimeout(this.debounceTimer);
      var query = this.input.value.trim();
      if (query.length < 2) { this.hide(); return; }
      this.debounceTimer = setTimeout(function() { self.fetchSuggestions(query); }, 300);
    },
    onFocus: function() {
      if (this.results.length > 0) this.show();
    },
    onKeydown: function(e) {
      if (!this.dropdown.classList.contains('active')) return;
      var items = this.dropdown.querySelectorAll('.search-item');
      var self = this;
      if (e.key === 'ArrowDown') {
        e.preventDefault();
        this.activeIndex = Math.min(this.activeIndex + 1, items.length - 1);
        this.updateActive(items);
      } else if (e.key === 'ArrowUp') {
        e.preventDefault();
        this.activeIndex = Math.max(this.activeIndex - 1, 0);
        this.updateActive(items);
      } else if (e.key === 'Enter') {
        e.preventDefault();
        if (this.activeIndex >= 0 && items[this.activeIndex]) {
          items[this.activeIndex].click();
        } else if (this.input.closest('form')) {
          this.input.closest('form').submit();
        }
      } else if (e.key === 'Escape') {
        this.hide();
      }
    },
    updateActive: function(items) {
      items.forEach(function(item, i) {
        item.classList.toggle('active', i === this.activeIndex);
      }.bind(this));
    },
    fetchSuggestions: function(query) {
      var self = this;
      fetch('ajax/search.php?action=suggest&q=' + encodeURIComponent(query))
        .then(function(res) {
          if (!res.ok) throw new Error('Search failed');
          return res.json();
        })
        .then(function(data) {
          self.results = data.products || [];
          self.render(data);
        })
        .catch(function() {
          self.results = [];
          self.hide();
        });
    },
    render: function(data) {
      var self = this;
      var html = '';
      if (data.suggestions && data.suggestions.length > 0) {
        html += '<div class="search-suggestion-label">Suggestions</div>';
        data.suggestions.forEach(function(s) {
          html += '<div class="search-item" data-query="' + self.escHtml(s) + '">' +
            '<i class="fas fa-search" style="color:var(--text-muted);width:40px;text-align:center;"></i>' +
            '<div class="item-info"><h4>' + self.escHtml(s) + '</h4></div></div>';
        });
      }
      if (data.products && data.products.length > 0) {
        html += '<div class="search-suggestion-label">Products</div>';
        data.products.forEach(function(p) {
          html += '<div class="search-item" onclick="location.href=\'product.php?slug=' + encodeURIComponent(p.slug || p.id) + '\'">' +
            '<img src="' + self.escHtml(p.image) + '" alt="' + self.escHtml(p.name) + '">' +
            '<div class="item-info"><h4>' + self.escHtml(p.name) + '</h4>' +
            '<span>$' + parseFloat(p.price).toFixed(2) + '</span></div></div>';
        });
      }
      if (!html) html = '<div class="search-suggestion-label">No results found</div>';
      this.dropdown.innerHTML = html;
      this.activeIndex = -1;
      this.dropdown.querySelectorAll('.search-item[data-query]').forEach(function(item) {
        item.addEventListener('click', function() {
          self.input.value = item.dataset.query;
          if (self.input.closest('form')) self.input.closest('form').submit();
        });
      });
      this.show();
    },
    show: function() { this.dropdown.classList.add('active'); },
    hide: function() { this.dropdown.classList.remove('active'); this.activeIndex = -1; },
    escHtml: function(str) {
      var div = document.createElement('div');
      div.textContent = str;
      return div.innerHTML;
    }
  };
  SearchModule.init();

  /* -------------------------------------------
     4. Toast Notifications
     ------------------------------------------- */
  window.Toast = {
    container: null,
    init: function() {
      this.container = document.querySelector('.toast-container');
      if (!this.container) {
        this.container = document.createElement('div');
        this.container.className = 'toast-container';
        document.body.appendChild(this.container);
      }
    },
    show: function(message, type, title) {
      type = type || 'info';
      title = title || type.charAt(0).toUpperCase() + type.slice(1);
      var icons = { success: 'fa-check', error: 'fa-xmark', warning: 'fa-exclamation', info: 'fa-info' };
      var toast = document.createElement('div');
      toast.className = 'toast ' + type;
      toast.innerHTML = '<div class="toast-icon"><i class="fas ' + (icons[type] || icons.info) + '"></i></div>' +
        '<div class="toast-content"><div class="toast-title">' + title + '</div>' +
        '<div class="toast-message">' + message + '</div></div>' +
        '<button class="toast-close" aria-label="Close"><i class="fas fa-xmark"></i></button>';
      var self = this;
      toast.querySelector('.toast-close').addEventListener('click', function() { self.dismiss(toast); });
      this.container.appendChild(toast);
      setTimeout(function() { self.dismiss(toast); }, 3500);
    },
    dismiss: function(toast) {
      if (!toast || toast.classList.contains('removing')) return;
      toast.classList.add('removing');
      setTimeout(function() { toast.remove(); }, 300);
    }
  };
  Toast.init();

  /* -------------------------------------------
     5. Cart Operations (AJAX)
     ------------------------------------------- */
  var Cart = {
    add: function(productId, quantity) {
      quantity = quantity || 1;
      fetch('ajax/cart.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'action=add&product_id=' + productId + '&quantity=' + quantity
      }).then(function(res) { return res.json(); })
        .then(function(data) {
          if (data.success) {
            Toast.show(data.message || 'Added to cart', 'success', 'Cart');
            Cart.updateBadge(data.cart_count);
          } else {
            Toast.show(data.message || 'Failed to add to cart', 'error', 'Error');
          }
        }).catch(function() { Toast.show('Network error. Please try again.', 'error'); });
    },
    updateQuantity: function(productId, quantity) {
      fetch('ajax/cart.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'action=update&product_id=' + productId + '&quantity=' + quantity
      }).then(function(res) { return res.json(); })
        .then(function(data) {
          if (data.success) {
            Cart.updateBadge(data.cart_count);
            var row = document.querySelector('[data-id="' + productId + '"]');
            if (row && data.subtotal_formatted !== undefined) {
              var priceEl = row.querySelector('.item-total-price');
              if (priceEl) priceEl.textContent = data.subtotal_formatted;
            }
            if (data.cart_total_formatted !== undefined) {
              var totalEl = document.querySelector('.cart-total-amount');
              if (totalEl) totalEl.textContent = data.cart_total_formatted;
            }
          } else {
            Toast.show(data.message || 'Update failed', 'error');
          }
        }).catch(function() { Toast.show('Network error', 'error'); });
    },
    remove: function(productId) {
      fetch('ajax/cart.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'action=remove&product_id=' + productId
      }).then(function(res) { return res.json(); })
        .then(function(data) {
          if (data.success) {
            Toast.show(data.message || 'Removed from cart', 'info', 'Cart');
            Cart.updateBadge(data.cart_count);
            location.reload();
          } else {
            Toast.show(data.message || 'Remove failed', 'error');
          }
        }).catch(function() { Toast.show('Network error', 'error'); });
    },
    updateBadge: function(count) {
      document.querySelectorAll('.cart-badge').forEach(function(badge) {
        badge.textContent = count;
        badge.classList.remove('pulse');
        void badge.offsetWidth;
        badge.classList.add('pulse');
      });
    }
  };

  document.addEventListener('click', function(e) {
    var addBtn = e.target.closest('.btn-add-cart, .add-to-cart-btn, [data-action="add-to-cart"]');
    if (addBtn) {
      if (addBtn.classList.contains('m-p-btn')) return;
      e.preventDefault();
      var id = addBtn.dataset.productId || addBtn.dataset.id || addBtn.dataset.cartId;
      var parent = addBtn.closest('.product-card, .product-info, .cart-item, .col-lg-6');
      var qtyInput = parent ? (parent.querySelector('.qty-input') || parent.querySelector('#qty')) : null;
      var qty = qtyInput ? parseInt(qtyInput.value) || 1 : 1;
      Cart.add(id, qty);
    }
    var removeBtn = e.target.closest('.item-remove, [data-action="remove-from-cart"], .remove-item');
    if (removeBtn) {
      e.preventDefault();
      var removeId = removeBtn.dataset.id || removeBtn.dataset.productId;
      if (!removeId) {
        var p = removeBtn.closest('[data-id]');
        if (p) removeId = p.dataset.id;
      }
      if (removeId && confirm('Remove this item from cart?')) Cart.remove(removeId);
    }
  });

  /* -------------------------------------------
     6. Wishlist
     ------------------------------------------- */
  var Wishlist = {
    toggle: function(productId, btn) {
      fetch('ajax/wishlist.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'action=toggle&product_id=' + productId
      }).then(function(res) { return res.json(); })
        .then(function(data) {
          if (data.success) {
            var added = data.status === 'added';
            if (btn) btn.classList.toggle('active', added);
            document.querySelectorAll('[data-wishlist-id="' + productId + '"]').forEach(function(b) {
              b.classList.toggle('active', added);
            });
            Toast.show(added ? 'Added to wishlist' : 'Removed from wishlist', 'success', 'Wishlist');
          } else if (data.require_login) {
            window.location.href = 'login.php';
          } else {
            Toast.show(data.message || 'Failed', 'error');
          }
        }).catch(function() { Toast.show('Network error', 'error'); });
    }
  };

  document.addEventListener('click', function(e) {
    var wishBtn = e.target.closest('.wishlist-btn, [data-action="toggle-wishlist"]');
    if (wishBtn) {
      e.preventDefault();
      e.stopPropagation();
      var id = wishBtn.dataset.productId || wishBtn.dataset.id || wishBtn.dataset.wishlistId;
      if (id) Wishlist.toggle(id, wishBtn);
    }
  });

  /* -------------------------------------------
     7. Quantity Stepper
     ------------------------------------------- */
  document.addEventListener('click', function(e) {
    var stepper = e.target.closest('.quantity-stepper');
    if (!stepper) return;
    var input = stepper.querySelector('.qty-input');
    if (!input) return;
    var min = parseInt(input.dataset.min) || 1;
    var max = parseInt(input.dataset.max) || 999;
    var val = parseInt(input.value) || min;
    if (e.target.closest('.qty-minus')) {
      val = Math.max(min, val - 1);
    } else if (e.target.closest('.qty-plus')) {
      val = Math.min(max, val + 1);
    } else { return; }
    input.value = val;
    var minusBtn = stepper.querySelector('.qty-minus');
    var plusBtn = stepper.querySelector('.qty-plus');
    if (minusBtn) minusBtn.disabled = val <= min;
    if (plusBtn) plusBtn.disabled = val >= max;
    var cartId = input.dataset.cartId || input.dataset.productId || input.dataset.id;
    if (cartId) Cart.updateQuantity(cartId, val);
    var priceEl = document.querySelector('.product-detail-section .current-price, .product-info .current-price');
    var totalEl = document.querySelector('.item-total-price, .cart-total-amount');
    if (priceEl && totalEl) {
      var unitPrice = parseFloat(priceEl.dataset.price) || parseFloat(priceEl.textContent.replace(/[^0-9.]/g, ''));
      if (unitPrice) totalEl.textContent = '$' + (unitPrice * val).toFixed(2);
    }
    input.dispatchEvent(new Event('change'));
  });

  document.addEventListener('input', function(e) {
    if (e.target.classList.contains('qty-input')) {
      var input = e.target;
      var min = parseInt(input.dataset.min) || 1;
      var max = parseInt(input.dataset.max) || 999;
      var val = parseInt(input.value);
      if (isNaN(val) || val < min) val = min;
      if (val > max) val = max;
      input.value = val;
      var stepper = input.closest('.quantity-stepper');
      if (stepper) {
        var mb = stepper.querySelector('.qty-minus');
        var pb = stepper.querySelector('.qty-plus');
        if (mb) mb.disabled = val <= min;
        if (pb) pb.disabled = val >= max;
      }
    }
  });

  /* -------------------------------------------
     8. Countdown Timer (Flash Sale)
     ------------------------------------------- */
  function countdownTimer(endDate, elementId) {
    var el = document.getElementById(elementId);
    if (!el) return;
    var end = new Date(endDate).getTime();
    function setVal(container, label, value) {
      var c = container.querySelector('[data-countdown="' + label + '"]');
      if (c && c.textContent !== value) {
        c.classList.add('flipping');
        setTimeout(function() { c.textContent = value; c.classList.remove('flipping'); }, 300);
      }
    }
    function update() {
      var diff = end - Date.now();
      if (diff <= 0) {
        el.querySelectorAll('.countdown-value').forEach(function(v) { v.textContent = '00'; });
        el.style.display = 'none';
        var b = el.closest('.flash-sale-banner, .flash-sale-section');
        if (b) b.style.display = 'none';
        return;
      }
      var d = Math.floor(diff / 86400000);
      var h = Math.floor((diff % 86400000) / 3600000);
      var m = Math.floor((diff % 3600000) / 60000);
      var s = Math.floor((diff % 60000) / 1000);
      var pad = function(n) { return String(n).padStart(2, '0'); };
      setVal(el, 'days', pad(d));
      setVal(el, 'hours', pad(h));
      setVal(el, 'minutes', pad(m));
      setVal(el, 'seconds', pad(s));
    }
    update();
    setInterval(update, 1000);
  }
  window.countdownTimer = countdownTimer;

  document.querySelectorAll('[data-countdown-end]').forEach(function(el) {
    var endDate = el.dataset.countdownEnd;
    var id = el.id || ('countdown-' + Math.random().toString(36).substr(2, 9));
    if (!el.id) el.id = id;
    countdownTimer(endDate, id);
  });

  /* -------------------------------------------
     9. Image Gallery (Product Detail)
     ------------------------------------------- */
  var Gallery = {
    mainImage: null,
    thumbnails: [],
    init: function() {
      this.mainImage = document.querySelector('.product-gallery .main-image img');
      this.thumbnails = document.querySelectorAll('.product-gallery .thumbnail');
      var self = this;
      this.thumbnails.forEach(function(thumb) {
        thumb.addEventListener('click', function() {
          var img = thumb.querySelector('img');
          var src = img ? img.src : null;
          if (!src || !self.mainImage) return;
          self.mainImage.style.opacity = '0';
          setTimeout(function() { self.mainImage.src = src; self.mainImage.style.opacity = '1'; }, 200);
          self.thumbnails.forEach(function(t) { t.classList.remove('active'); });
          thumb.classList.add('active');
        });
      });
      var mainImgContainer = document.querySelector('.product-gallery .main-image');
      if (mainImgContainer) {
        mainImgContainer.addEventListener('click', function() {
          self.openLightbox(self.mainImage ? self.mainImage.src : null);
        });
      }
    },
    openLightbox: function(src) {
      if (!src) return;
      var overlay = document.createElement('div');
      overlay.className = 'modal-overlay active';
      overlay.innerHTML = '<div style="max-width:90vw;max-height:90vh;cursor:zoom-out;">' +
        '<img src="' + src + '" style="max-width:100%;max-height:85vh;border-radius:var(--radius-md);box-shadow:var(--shadow-xl);"></div>';
      overlay.addEventListener('click', function(e) {
        if (e.target === overlay || e.target.closest('div')) overlay.remove();
      });
      document.body.appendChild(overlay);
    }
  };
  Gallery.init();

  /* -------------------------------------------
     9.5 Blinkit-Style Horizontal Scroll with Drag
     ------------------------------------------- */
  document.querySelectorAll('.b-prod-row').forEach(function(row) {
    var isDown = false, startX, scrollLeft;
    row.addEventListener('mousedown', function(e) {
      isDown = true;
      row.classList.add('dragging');
      startX = e.pageX - row.offsetLeft;
      scrollLeft = row.scrollLeft;
    });
    row.addEventListener('mouseleave', function() {
      isDown = false;
      row.classList.remove('dragging');
    });
    row.addEventListener('mouseup', function() {
      isDown = false;
      row.classList.remove('dragging');
    });
    row.addEventListener('mousemove', function(e) {
      if (!isDown) return;
      e.preventDefault();
      var x = e.pageX - row.offsetLeft;
      var walk = (x - startX) * 2;
      row.scrollLeft = scrollLeft - walk;
    });
  });

  /* Smooth reveal for Blinkit product cards on scroll */
  var bAnimObserver = new IntersectionObserver(function(entries) {
    entries.forEach(function(entry) {
      if (entry.isIntersecting) {
        entry.target.classList.add('visible');
        bAnimObserver.unobserve(entry.target);
      }
    });
  }, { threshold: 0.1, rootMargin: '0px 0px -50px 0px' });
  document.querySelectorAll('.b-anim').forEach(function(el) {
    bAnimObserver.observe(el);
  });

  /* -------------------------------------------
     10. Smooth Scroll & Back to Top
     ------------------------------------------- */
  document.querySelectorAll('a[href^="#"]').forEach(function(anchor) {
    anchor.addEventListener('click', function(e) {
      var href = this.getAttribute('href');
      if (href === '#') return;
      var target = document.querySelector(href);
      if (target) { e.preventDefault(); target.scrollIntoView({ behavior: 'smooth', block: 'start' }); }
    });
  });
  var scrollToTopBtn = document.querySelector('.scroll-to-top');
  if (scrollToTopBtn) {
    window.addEventListener('scroll', function() {
      scrollToTopBtn.classList.toggle('visible', window.scrollY > 400);
    }, { passive: true });
    scrollToTopBtn.addEventListener('click', function() { window.scrollTo({ top: 0, behavior: 'smooth' }); });
  }

  /* -------------------------------------------
     11. Form Validation
     ------------------------------------------- */
  var Validator = {
    rules: {
      required: function(v) { return v.trim().length > 0 || 'This field is required'; },
      email: function(v) { return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v) || 'Enter a valid email'; },
      minLength: function(m) { return function(v) { return v.length >= m || 'Minimum ' + m + ' characters'; }; },
      maxLength: function(m) { return function(v) { return v.length <= m || 'Maximum ' + m + ' characters'; }; },
      password: function(v) { return v.length >= 6 || 'Password must be at least 6 characters'; },
      confirmPassword: function(f) { return function(v) { var m = document.querySelector(f); return m && v === m.value || 'Passwords do not match'; }; },
      phone: function(v) { return /^[\d\s\-\+\(\)]{7,15}$/.test(v) || 'Enter a valid phone number'; },
      numeric: function(v) { return /^\d+(\.\d+)?$/.test(v) || 'Enter a valid number'; }
    },
    validate: function(form) {
      var isValid = true;
      form.querySelectorAll('[data-validate]').forEach(function(input) {
        var rules = input.dataset.validate.split('|');
        var group = input.closest('.form-group');
        if (!group) return;
        group.classList.remove('error', 'success');
        for (var i = 0; i < rules.length; i++) {
          var parts = rules[i].split(':');
          var fn = Validator.rules[parts[0]];
          if (!fn) continue;
          var checker = parts[1] ? fn(parts[1]) : fn;
          var result = checker(input.value);
          if (result !== true) {
            group.classList.add('error');
            var err = group.querySelector('.error-message');
            if (err) err.textContent = result;
            isValid = false;
            break;
          } else { group.classList.add('success'); }
        }
      });
      return isValid;
    },
    init: function() {
      document.querySelectorAll('form[data-validate-form]').forEach(function(form) {
        form.addEventListener('submit', function(e) { if (!Validator.validate(form)) e.preventDefault(); });
        form.querySelectorAll('[data-validate]').forEach(function(input) {
          input.addEventListener('blur', function() {
            var group = input.closest('.form-group');
            if (!group) return;
            var rules = input.dataset.validate.split('|');
            group.classList.remove('error', 'success');
            for (var i = 0; i < rules.length; i++) {
              var parts = rules[i].split(':');
              var fn = Validator.rules[parts[0]];
              if (!fn) continue;
              var checker = parts[1] ? fn(parts[1]) : fn;
              var result = checker(input.value);
              if (result !== true) {
                group.classList.add('error');
                var err = group.querySelector('.error-message');
                if (err) err.textContent = result;
                break;
              } else { group.classList.add('success'); }
            }
          });
        });
      });
    }
  };
  Validator.init();

  /* -------------------------------------------
     12. Coupon Application
     ------------------------------------------- */
  window.applyCoupon = function() {
    var input = document.querySelector('.coupon-form input, [name="coupon_code"]');
    var btn = document.querySelector('.coupon-form button, [data-action="apply-coupon"]');
    if (!input) return;
    var code = input.value.trim();
    if (!code) { Toast.show('Please enter a coupon code', 'warning'); return; }
    var origText = '';
    if (btn) { btn.disabled = true; origText = btn.innerHTML; btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>'; }
    fetch('ajax/coupon.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: 'action=apply&coupon_code=' + encodeURIComponent(code)
    }).then(function(r) { return r.json(); })
      .then(function(data) {
        if (data.success) {
          Toast.show(data.message || 'Coupon applied!', 'success', 'Discount');
          if (data.discount !== undefined) {
            var de = document.querySelector('.discount-amount, .coupon-discount');
            if (de) de.textContent = '-$' + parseFloat(data.discount).toFixed(2);
          }
          if (data.total !== undefined) {
            var te = document.querySelector('.cart-total-amount, .order-total');
            if (te) te.textContent = '$' + parseFloat(data.total).toFixed(2);
          }
          if (data.coupon_row) {
            var cr = document.querySelector('.coupon-row');
            if (cr) cr.style.display = 'flex';
          }
        } else { Toast.show(data.message || 'Invalid coupon code', 'error'); }
      }).catch(function() { Toast.show('Network error', 'error'); })
      .finally(function() { if (btn) { btn.disabled = false; btn.innerHTML = origText || 'Apply'; } });
  };
  document.addEventListener('click', function(e) {
    if (e.target.closest('[data-action="apply-coupon"]')) { e.preventDefault(); window.applyCoupon(); }
  });

  /* -------------------------------------------
     13. Checkout (Multi-step)
     ------------------------------------------- */
  var Checkout = {
    currentStep: 1,
    totalSteps: 3,
    init: function() {
      if (document.querySelectorAll('.checkout-steps .step').length === 0) return;
      var self = this;
      document.addEventListener('click', function(e) {
        if (e.target.closest('[data-action="checkout-next"]')) {
          e.preventDefault();
          if (self.validateStep(self.currentStep)) self.goToStep(self.currentStep + 1);
        }
        if (e.target.closest('[data-action="checkout-prev"]')) {
          e.preventDefault();
          self.goToStep(self.currentStep - 1);
        }
        var ac = e.target.closest('.address-card');
        if (ac) {
          document.querySelectorAll('.address-card').forEach(function(c) { c.classList.remove('selected'); });
          ac.classList.add('selected');
        }
        var pc = e.target.closest('.payment-method-card');
        if (pc) {
          document.querySelectorAll('.payment-method-card').forEach(function(c) { c.classList.remove('selected'); });
          pc.classList.add('selected');
        }
        if (e.target.closest('[data-action="place-order"]')) { e.preventDefault(); self.placeOrder(); }
      });
      this.goToStep(1);
    },
    goToStep: function(step) {
      if (step < 1 || step > this.totalSteps) return;
      this.currentStep = step;
      document.querySelectorAll('.checkout-steps .step').forEach(function(el, i) {
        el.classList.remove('active', 'completed');
        if (i + 1 < step) el.classList.add('completed');
        if (i + 1 === step) el.classList.add('active');
      });
      document.querySelectorAll('.checkout-steps .step-connector').forEach(function(el, i) {
        el.classList.toggle('active', i + 1 < step);
      });
      document.querySelectorAll('.checkout-step-content').forEach(function(el, i) {
        el.classList.toggle('active', i + 1 === step);
      });
      window.scrollTo({ top: 0, behavior: 'smooth' });
    },
    validateStep: function(step) {
      var content = document.querySelector('.checkout-step-content[data-step="' + step + '"]');
      if (!content) return true;
      var valid = true;
      content.querySelectorAll('[data-validate]').forEach(function(field) {
        var group = field.closest('.form-group');
        if (!group) return;
        if (!field.value.trim()) {
          group.classList.add('error');
          var err = group.querySelector('.error-message');
          if (err) err.textContent = 'This field is required';
          valid = false;
        } else { group.classList.remove('error'); }
      });
      if (!valid) Toast.show('Please fill in all required fields', 'warning');
      return valid;
    },
    placeOrder: function() {
      var btn = document.querySelector('[data-action="place-order"]');
      if (!btn) return;
      var addr = document.querySelector('.address-card.selected');
      var pay = document.querySelector('.payment-method-card.selected');
      if (!addr) { Toast.show('Please select a delivery address', 'warning'); return; }
      if (!pay) { Toast.show('Please select a payment method', 'warning'); return; }
      btn.disabled = true;
      btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Placing Order...';
      fetch('ajax/orders.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'action=place_order&address_id=' + addr.dataset.addressId + '&payment_method=' + pay.dataset.paymentMethod
      }).then(function(r) { return r.json(); })
        .then(function(data) {
          if (data.success) {
            Toast.show('Order placed successfully!', 'success', 'Success');
            setTimeout(function() { window.location.href = data.redirect || 'orders.php?success=1'; }, 1000);
          } else {
            Toast.show(data.message || 'Failed to place order', 'error');
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-lock"></i> Place Order';
          }
        }).catch(function() {
          Toast.show('Network error. Please try again.', 'error');
          btn.disabled = false;
          btn.innerHTML = '<i class="fas fa-lock"></i> Place Order';
        });
    }
  };
  Checkout.init();

  /* -------------------------------------------
     14. Compare Products
     ------------------------------------------- */
  var Compare = {
    key: 'compare_products',
    maxItems: 4,
    getItems: function() { try { return JSON.parse(localStorage.getItem(this.key)) || []; } catch (e) { return []; } },
    save: function(items) { localStorage.setItem(this.key, JSON.stringify(items)); this.updateBar(); },
    add: function(id, name, image) {
      var items = this.getItems();
      if (items.find(function(i) { return i.id == id; })) { Toast.show('Already in comparison', 'info'); return; }
      if (items.length >= this.maxItems) { Toast.show('Maximum ' + this.maxItems + ' products to compare', 'warning'); return; }
      items.push({ id: id, name: name, image: image });
      this.save(items);
      Toast.show('Added to comparison', 'success');
    },
    remove: function(id) { this.save(this.getItems().filter(function(i) { return i.id != id; })); },
    updateBar: function() {
      var bar = document.querySelector('.compare-bar');
      if (!bar) return;
      var items = this.getItems();
      if (items.length === 0) { bar.classList.remove('active'); return; }
      bar.classList.add('active');
      var container = bar.querySelector('.compare-items');
      if (!container) return;
      container.innerHTML = items.map(function(item) {
        return '<div class="compare-item" data-compare-id="' + item.id + '">' +
          '<img src="' + item.image + '" alt="' + item.name + '">' +
          '<span style="font-size:0.82rem;font-weight:500;color:var(--text-primary);">' + item.name + '</span>' +
          '<button class="remove-compare" data-compare-remove="' + item.id + '"><i class="fas fa-xmark"></i></button></div>';
      }).join('');
      var countEl = bar.querySelector('.compare-count');
      if (countEl) countEl.textContent = items.length;
    },
    init: function() { this.updateBar(); }
  };
  Compare.init();
  document.addEventListener('click', function(e) {
    var cb = e.target.closest('[data-action="compare"], .btn-compare');
    if (cb) { e.preventDefault(); Compare.add(cb.dataset.productId, cb.dataset.productName || 'Product', cb.dataset.productImage || ''); }
    var rc = e.target.closest('[data-compare-remove]');
    if (rc) { e.preventDefault(); Compare.remove(rc.dataset.compareRemove); }
  });

  /* -------------------------------------------
     15. Skeleton Loader
     ------------------------------------------- */
  window.showSkeleton = function(container, count) {
    count = count || 4;
    if (!container) return;
    var html = '';
    for (var i = 0; i < count; i++) {
      html += '<div class="skeleton-card"><div class="skeleton skeleton-image"></div>' +
        '<div class="skeleton-body"><div class="skeleton skeleton-text short"></div>' +
        '<div class="skeleton skeleton-title"></div><div class="skeleton skeleton-text medium"></div>' +
        '<div class="skeleton skeleton-price"></div></div></div>';
    }
    container.innerHTML = html;
  };
  window.hideSkeleton = function(container, html) { if (container) container.innerHTML = html || ''; };

  /* -------------------------------------------
     16. Product Filtering & Sorting
     ------------------------------------------- */
  var ProductFilter = {
    init: function() {
      var form = document.querySelector('#filter-form, .filter-form');
      if (!form) return;
      form.querySelectorAll('select[name="sort"], select[name="category"], select[name="brand"]').forEach(function(s) {
        s.addEventListener('change', function() { ProductFilter.applyFilters(); });
      });
      var ps = form.querySelector('[name="min_price"], [name="max_price"]');
      if (ps) ps.addEventListener('change', function() { ProductFilter.applyFilters(); });
      form.querySelectorAll('.filter-checkbox').forEach(function(cb) {
        cb.addEventListener('change', function() { ProductFilter.applyFilters(); });
      });
    },
    applyFilters: function() {
      var form = document.querySelector('#filter-form, .filter-form');
      if (!form) return;
      var params = new URLSearchParams();
      new FormData(form).forEach(function(v, k) { if (v) params.set(k, v); });
      var grid = document.querySelector('.products-grid');
      if (grid) window.showSkeleton(grid, 6);
      var url = window.location.pathname + '?' + params.toString();
      window.history.pushState({}, '', url);
      fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
        .then(function(r) { return r.text(); })
        .then(function(html) {
          var doc = new DOMParser().parseFromString(html, 'text/html');
          var ng = doc.querySelector('.products-grid');
          var np = doc.querySelector('.pagination');
          if (ng && grid) grid.innerHTML = ng.innerHTML;
          if (np) { var p = document.querySelector('.pagination'); if (p) p.innerHTML = np.innerHTML; }
        }).catch(function() { window.location.reload(); });
    }
  };
  ProductFilter.init();

  /* -------------------------------------------
     17. Mega Menu
     ------------------------------------------- */
  var MegaMenu = {
    activeMenu: null,
    timeout: null,
    init: function() {
      var self = this;
      document.querySelectorAll('[data-mega-menu]').forEach(function(trigger) {
        trigger.addEventListener('mouseenter', function() {
          clearTimeout(self.timeout);
          var menu = document.getElementById(trigger.dataset.megaMenu);
          if (!menu) return;
          self.closeAll();
          menu.classList.add('active');
          self.activeMenu = menu;
        });
        trigger.addEventListener('mouseleave', function() {
          self.timeout = setTimeout(function() {
            if (self.activeMenu && !self.activeMenu.matches(':hover')) {
              self.activeMenu.classList.remove('active');
              self.activeMenu = null;
            }
          }, 200);
        });
      });
      document.querySelectorAll('.mega-menu').forEach(function(menu) {
        menu.addEventListener('mouseenter', function() { clearTimeout(self.timeout); });
        menu.addEventListener('mouseleave', function() { menu.classList.remove('active'); self.activeMenu = null; });
      });
    },
    closeAll: function() { document.querySelectorAll('.mega-menu').forEach(function(m) { m.classList.remove('active'); }); }
  };
  MegaMenu.init();

  /* -------------------------------------------
     18. Mobile Sidebar
     ------------------------------------------- */
  var MobileSidebar = {
    overlay: document.querySelector('.mobile-sidebar-overlay'),
    sidebar: document.querySelector('.mobile-sidebar'),
    init: function() {
      var self = this;
      document.addEventListener('click', function(e) {
        if (e.target.closest('.mobile-menu-toggle')) self.open();
        if (e.target.closest('.sidebar-close') || e.target === self.overlay) self.close();
      });
    },
    open: function() {
      if (this.overlay) this.overlay.classList.add('active');
      if (this.sidebar) this.sidebar.classList.add('active');
      document.body.style.overflow = 'hidden';
    },
    close: function() {
      if (this.overlay) this.overlay.classList.remove('active');
      if (this.sidebar) this.sidebar.classList.remove('active');
      document.body.style.overflow = '';
    }
  };
  MobileSidebar.init();

  /* -------------------------------------------
     19. User Dropdown
     ------------------------------------------- */
  document.addEventListener('click', function(e) {
    var userBtn = e.target.closest('.user-avatar-btn');
    if (userBtn) {
      e.stopPropagation();
      var dd = userBtn.closest('.user-dropdown-wrapper').querySelector('.user-dropdown');
      if (dd) dd.classList.toggle('active');
      return;
    }
    document.querySelectorAll('.user-dropdown').forEach(function(d) { d.classList.remove('active'); });
  });

  /* -------------------------------------------
     20. Admin Panel Features
     ------------------------------------------- */
  var adminSidebar = document.querySelector('.dashboard-sidebar');
  var sidebarToggle = document.querySelector('.dashboard-sidebar .sidebar-toggle');
  if (sidebarToggle && adminSidebar) {
    sidebarToggle.addEventListener('click', function() { adminSidebar.classList.toggle('active'); });
  }

  var tableSearch = document.querySelector('.data-table-search input');
  if (tableSearch) {
    tableSearch.addEventListener('input', function() {
      var q = this.value.toLowerCase();
      document.querySelectorAll('.data-table tbody tr').forEach(function(row) {
        row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
      });
    });
  }

  document.addEventListener('click', function(e) {
    var deleteBtn = e.target.closest('[data-action="delete"], .delete-btn');
    if (deleteBtn) {
      e.preventDefault();
      var id = deleteBtn.dataset.id;
      var name = deleteBtn.dataset.name || 'this item';
      var overlay = document.createElement('div');
      overlay.className = 'modal-overlay active';
      overlay.innerHTML = '<div class="modal-box"><div class="modal-icon danger"><i class="fas fa-trash"></i></div>' +
        '<h3>Delete Confirmation</h3><p>Are you sure you want to delete <strong>' + name + '</strong>? This cannot be undone.</p>' +
        '<div class="modal-actions"><button class="btn btn-outline modal-cancel-btn">Cancel</button>' +
        '<button class="btn btn-secondary" id="confirm-delete-btn">Delete</button></div></div>';
      document.body.appendChild(overlay);
      overlay.querySelector('.modal-cancel-btn').addEventListener('click', function() { overlay.remove(); });
      overlay.addEventListener('click', function(ev) { if (ev.target === overlay) overlay.remove(); });
      overlay.querySelector('#confirm-delete-btn').addEventListener('click', function() {
        overlay.remove();
        var form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = '<input type="hidden" name="delete_id" value="' + id + '">';
        document.body.appendChild(form);
        form.submit();
      });
    }
  });

  document.querySelectorAll('input[type="file"][data-preview]').forEach(function(input) {
    input.addEventListener('change', function() {
      var preview = document.getElementById(this.dataset.preview);
      if (!preview || !this.files || !this.files[0]) return;
      var reader = new FileReader();
      reader.onload = function(e) { preview.src = e.target.result; preview.style.display = 'block'; };
      reader.readAsDataURL(this.files[0]);
    });
  });

  if (typeof Chart !== 'undefined') {
    document.querySelectorAll('[data-chart]').forEach(function(canvas) {
      var type = canvas.dataset.chartType || 'line';
      try {
        var data = JSON.parse(canvas.dataset.chartData);
        new Chart(canvas.getContext('2d'), {
          type: type, data: data,
          options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { labels: { color: '#a0a0b8', font: { family: 'Poppins' } } } },
            scales: (type !== 'doughnut' && type !== 'pie') ? {
              x: { ticks: { color: '#a0a0b8' }, grid: { color: 'rgba(255,255,255,0.05)' } },
              y: { ticks: { color: '#a0a0b8' }, grid: { color: 'rgba(255,255,255,0.05)' } }
            } : undefined
          }
        });
      } catch (err) { console.warn('Chart.js parse error:', err); }
    });
  }

  /* -------------------------------------------
     21. Lazy Load Images
     ------------------------------------------- */
  if ('IntersectionObserver' in window) {
    var imgObs = new IntersectionObserver(function(entries) {
      entries.forEach(function(entry) {
        if (entry.isIntersecting) {
          var img = entry.target;
          if (img.dataset.src) { img.src = img.dataset.src; img.removeAttribute('data-src'); }
          imgObs.unobserve(img);
        }
      });
    }, { rootMargin: '50px' });
    document.querySelectorAll('img[data-src]').forEach(function(img) { imgObs.observe(img); });
  }

  /* -------------------------------------------
     22. Tab Navigation
     ------------------------------------------- */
  document.addEventListener('click', function(e) {
    var tabBtn = e.target.closest('[data-tab]');
    if (!tabBtn) return;
    var tabId = tabBtn.dataset.tab;
    var group = tabBtn.closest('.tabs, [data-tab-group]');
    if (group) {
      group.querySelectorAll('[data-tab]').forEach(function(t) { t.classList.remove('active'); });
      group.querySelectorAll('.tab-pane').forEach(function(p) { p.classList.remove('active'); });
    }
    tabBtn.classList.add('active');
    var pane = document.getElementById(tabId);
    if (pane) pane.classList.add('active');
  });

  /* -------------------------------------------
     23. Accordion
     ------------------------------------------- */
  document.addEventListener('click', function(e) {
    var trigger = e.target.closest('.accordion-header, [data-accordion]');
    if (!trigger) return;
    var item = trigger.closest('.accordion-item');
    if (!item) return;
    var content = item.querySelector('.accordion-content, .accordion-body');
    if (!content) return;
    var isOpen = item.classList.contains('open');
    var acc = item.closest('.accordion');
    if (acc) {
      acc.querySelectorAll('.accordion-item').forEach(function(ai) {
        ai.classList.remove('open');
        var ac = ai.querySelector('.accordion-content, .accordion-body');
        if (ac) ac.style.maxHeight = null;
      });
    }
    if (!isOpen) { item.classList.add('open'); content.style.maxHeight = content.scrollHeight + 'px'; }
  });

  /* -------------------------------------------
     24. Tooltip
     ------------------------------------------- */
  document.querySelectorAll('[data-tooltip]').forEach(function(el) {
    el.style.position = 'relative';
    el.addEventListener('mouseenter', function() {
      var tip = document.createElement('div');
      tip.className = 'tooltip-popup';
      tip.textContent = el.dataset.tooltip;
      tip.style.cssText = 'position:absolute;bottom:120%;left:50%;transform:translateX(-50%);padding:6px 12px;background:var(--bg-card);color:var(--text-primary);border-radius:var(--radius-sm);font-size:0.78rem;white-space:nowrap;box-shadow:var(--shadow-md);z-index:1000;pointer-events:none;animation:fadeUp 0.2s ease;border:1px solid var(--border-glass);';
      el.appendChild(tip);
    });
    el.addEventListener('mouseleave', function() {
      var tip = el.querySelector('.tooltip-popup');
      if (tip) tip.remove();
    });
  });

  /* -------------------------------------------
     25. Back Button
     ------------------------------------------- */
  var backBtn = document.querySelector('[data-action="go-back"]');
  if (backBtn) backBtn.addEventListener('click', function() { window.history.back(); });

  /* -------------------------------------------
     Console Welcome
     ------------------------------------------- */
  console.log('%c AI Shopping %c Loaded Successfully ',
    'background:#6C63FF;color:#fff;padding:6px 12px;border-radius:4px 0 0 4px;font-weight:bold;',
    'background:#FF6584;color:#fff;padding:6px 12px;border-radius:0 4px 4px 0;');

  /* -------------------------------------------
     26. Global 3D Tilt on Mouse Hover
     ------------------------------------------- */
  (function initGlobal3DTilt() {
    var selectors = [
      '.product-card', '.category-card', '.featured-card',
      '.deals-card', '.deal-card', '.brand-card',
      '.review-card', '.review-item', '.wishlist-card',
      '.cart-item', '.order-card', '.compare-card',
      '.testimonial-card', '.blog-card',
      '.card', '.list-group-item',
      '.btn', '.nav-link', '.category-item',
      '.hero-slide', '.promo-card', '.newsletter-section',
      '.slider-nav', '.offer-slide', '.swiper-slide',
      '.b-3d-wrap', '.b-sale-wrap', '.b-poster-wrap',
      '.b-rain-wrap', '.b-apple-wrap', '.b-glass-wrap',
      '.sale-card', '.pg-card', '.rc-card', '.gl-card',
      '.m-promo-card', '.m-feat-item'
    ];
    var maxTilt = 12;
    var maxShift = 6;
    var perspective = 800;

    // Fix overflow on sliders so 3D works
    document.querySelectorAll('.product-slider').forEach(function(sl) {
      sl.style.overflowX = 'clip';
    });

    function attach(el) {
      if (el._tilt3dAttached) return;
      el._tilt3dAttached = true;
      el.style.transformStyle = 'preserve-3d';
      el.style.transition = 'transform 0.2s ease-out, box-shadow 0.3s ease';
      el.style.willChange = 'transform';

      var isBanner = el.classList.contains('b-3d-wrap') || el.classList.contains('b-sale-wrap') ||
                     el.classList.contains('b-poster-wrap') || el.classList.contains('b-rain-wrap') ||
                     el.classList.contains('b-apple-wrap') || el.classList.contains('b-glass-wrap');
      var bannerTilt = isBanner ? 8 : maxTilt;
      var bannerShift = isBanner ? 4 : maxShift;

      el.addEventListener('mousemove', function(e) {
        var r = el.getBoundingClientRect();
        var x = (e.clientX - r.left) / r.width;
        var y = (e.clientY - r.top) / r.height;
        var tiltX = (0.5 - y) * bannerTilt;
        var tiltY = (x - 0.5) * bannerTilt;
        var shiftX = (x - 0.5) * bannerShift;
        var shiftY = (0.5 - y) * bannerShift;

        el.style.transform = 'perspective(' + perspective + 'px) rotateX(' + tiltX + 'deg) rotateY(' + tiltY + 'deg) translateX(' + shiftX + 'px) translateY(' + shiftY + 'px) scale3d(1.01,1.01,1.01)';
        el.classList.add('tilt-3d-hover');

        var cx = x - 0.5;
        var cy = y - 0.5;

        if (isBanner) {
          // Banner: parallax text block (push back)
          var textBlock = el.querySelector('.b-3d-text, .b-poster-text, .b-rain-text, .b-apple-text, .b-glass-header');
          if (textBlock) textBlock.style.transform = 'translateZ(20px) translateX(' + (cx * -8) + 'px) translateY(' + (cy * -8) + 'px)';

          // Banner: parallax visual block (pull forward)
          var visualBlock = el.querySelector('.b-3d-visual, .b-poster-grid, .b-rain-grid, .b-apple-visual, .b-glass-grid');
          if (visualBlock) visualBlock.style.transform = 'translateZ(30px) translateX(' + (cx * 12) + 'px) translateY(' + (cy * -12) + 'px)';

          // Banner: badges float
          var badges = el.querySelectorAll('.b-label, .bp-badge, .r-badge, .apple-tag, .hero-3d-badge, .card-tag');
          for (var b = 0; b < badges.length; b++) {
            badges[b].style.transform = 'translateZ(35px) translateY(' + (cy * -4) + 'px)';
          }

          // Banner: buttons pop
          var btns = el.querySelectorAll('.b-btn, .r-btn, .apple-btn');
          for (var bt = 0; bt < btns.length; bt++) {
            btns[bt].style.transform = 'translateZ(25px) translateY(' + (cy * -3) + 'px)';
          }

          // Banner: podiums / displays tilt extra
          var podiums = el.querySelectorAll('.b-3d-podium, .apple-display');
          for (var p = 0; p < podiums.length; p++) {
            podiums[p].style.transform = 'perspective(400px) rotateY(' + (cx * 15) + 'deg) rotateX(' + (cy * -10) + 'deg) translateZ(15px)';
          }

          // Banner: floor shadow shifts
          var floor = el.querySelector('.b-3d-floor');
          if (floor) {
            floor.style.transform = 'translateX(calc(-50% + ' + (cx * 20) + 'px)) translateZ(-10px)';
            floor.style.opacity = '0.8';
          }

          // Banner: background grid parallax
          var grid = el.querySelector('.b-3d-grid, .hero-3d-grid');
          if (grid) grid.style.transform = 'translateX(calc(-50% + ' + (cx * -10) + 'px)) translateY(' + (cy * -10) + 'px)';

          // Banner: background orbs parallax
          var bgs = el.querySelectorAll('.b-3d-bg span, .b-sale-bg span, .b-poster-bg span, .b-rain-bg span, .apple-glow, .apple-glow2');
          for (var g = 0; g < bgs.length; g++) {
            var gDepth = 5 + g * 3;
            bgs[g].style.transform = 'translate(' + (cx * gDepth) + 'px,' + (cy * gDepth) + 'px)';
          }

          // Banner: product images deep parallax
          var prodImgs = el.querySelectorAll('.b-3d-podium .pedestal img, .apple-display .ap-product, .sale-card img, .pg-card img, .rc-card img, .gl-card img');
          for (var pi = 0; pi < prodImgs.length; pi++) {
            var piDepth = 15 + pi * 5;
            prodImgs[pi].style.transform = 'translateZ(' + piDepth + 'px) translateX(' + (cx * 6) + 'px) translateY(' + (cy * -6) + 'px) scale(1.06)';
            prodImgs[pi].style.filter = 'brightness(1.08) drop-shadow(0 8px 20px rgba(0,0,0,0.2))';
          }

          // Banner: inner cards (sale-card, pg-card etc.) individual parallax
          var cards = el.querySelectorAll('.sale-card, .pg-card, .rc-card, .gl-card');
          for (var sc = 0; sc < cards.length; sc++) {
            var scDepth = 8 + (sc % 4) * 4;
            cards[sc].style.transform = 'translateZ(' + scDepth + 'px) translateY(' + (cy * -4) + 'px)';
          }

          // Countdown boxes pop
          var cdBoxes = el.querySelectorAll('.cd-box');
          for (var cb = 0; cb < cdBoxes.length; cb++) {
            cdBoxes[cb].style.transform = 'translateZ(20px)';
          }
        } else {
          // Generic: parallax children
          var children = el.querySelectorAll('img, h3, h4, h5, h6, p, span, .btn, .card-title, .card-text, small, .badge');
          for (var i = 0; i < children.length; i++) {
            var c = children[i];
            var depth = (i % 3 + 1) * 4;
            c.style.transform = 'translateZ(' + depth + 'px) translateX(' + (cx * 3) + 'px) translateY(' + (cy * -3) + 'px)';
          }

          // image pop
          var imgs = el.querySelectorAll('img');
          for (var j = 0; j < imgs.length; j++) {
            imgs[j].style.transform = 'translateZ(12px) scale(1.05)';
            imgs[j].style.filter = 'brightness(1.05)';
          }
        }
      });

      el.addEventListener('mouseleave', function() {
        el.style.transform = '';
        el.classList.remove('tilt-3d-hover');

        if (isBanner) {
          var allChildren = el.querySelectorAll('[style*="transform"], [style*="filter"]');
          for (var i = 0; i < allChildren.length; i++) {
            if (allChildren[i] !== el) {
              allChildren[i].style.transform = '';
              allChildren[i].style.filter = '';
            }
          }
        } else {
          var children = el.querySelectorAll('img, h3, h4, h5, h6, p, span, .btn, .card-title, .card-text, small, .badge');
          for (var i = 0; i < children.length; i++) {
            children[i].style.transform = '';
          }
          var imgs = el.querySelectorAll('img');
          for (var j = 0; j < imgs.length; j++) {
            imgs[j].style.transform = '';
            imgs[j].style.filter = '';
          }
        }
      });
    }

    function scanAndAttach() {
      var all = document.querySelectorAll(selectors.join(','));
      for (var i = 0; i < all.length; i++) attach(all[i]);
    }

    scanAndAttach();

    // re-scan on dynamic content
    var observer = new MutationObserver(function() {
      scanAndAttach();
      // re-fix sliders
      document.querySelectorAll('.product-slider').forEach(function(sl) {
        sl.style.overflowX = 'clip';
      });
    });
    observer.observe(document.body, { childList: true, subtree: true });
  })();

});