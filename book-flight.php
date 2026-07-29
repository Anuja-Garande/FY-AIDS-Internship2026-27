<?php
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'includes/functions.php';

$page_title = 'Book Flights';
?>
<?php require_once 'includes/header.php'; ?>

<section class="flight-hero">
  <div class="container text-center">
    <h1 class="mb-3"><i class="fas fa-plane-departure me-2"></i>Book Your Flight</h1>
    <p class="mb-0">Find and book domestic & international flights at the best prices</p>
    <div class="flight-features">
      <div class="flight-feature"><i class="fas fa-shield-alt"></i> Secure Booking</div>
      <div class="flight-feature"><i class="fas fa-percent"></i> Best Price Guarantee</div>
      <div class="flight-feature"><i class="fas fa-headset"></i> 24/7 Support</div>
      <div class="flight-feature"><i class="fas fa-undo"></i> Easy Cancellation</div>
    </div>
  </div>
</section>

<div class="container">

  <div class="row g-4 mt-4 mb-5">
    <div class="col-md-4">
      <div class="text-center p-4" style="background:var(--bg-card);border-radius:var(--radius-md);border:1px solid var(--border-color);">
        <i class="fas fa-search-dollar fa-2x mb-3" style="color:var(--primary);"></i>
        <h6 class="fw-bold" style="color:var(--text-heading);">Compare Prices</h6>
        <p class="text-muted small mb-0">We compare across multiple airlines to get you the best fare available.</p>
      </div>
    </div>
    <div class="col-md-4">
      <div class="text-center p-4" style="background:var(--bg-card);border-radius:var(--radius-md);border:1px solid var(--border-color);">
        <i class="fas fa-lock fa-2x mb-3" style="color:var(--primary);"></i>
        <h6 class="fw-bold" style="color:var(--text-heading);">Secure Payments</h6>
        <p class="text-muted small mb-0">Your payment details are encrypted and securely processed.</p>
      </div>
    </div>
    <div class="col-md-4">
      <div class="text-center p-4" style="background:var(--bg-card);border-radius:var(--radius-md);border:1px solid var(--border-color);">
        <i class="fas fa-headset fa-2x mb-3" style="color:var(--primary);"></i>
        <h6 class="fw-bold" style="color:var(--text-heading);">24/7 Assistance</h6>
        <p class="text-muted small mb-0">Our travel experts are available round the clock to help you.</p>
      </div>
    </div>
  </div>

  <div class="text-center mb-3">
    <h3 class="fw-bold" style="color:var(--text-heading);"><i class="fas fa-chair me-2" style="color:var(--primary);"></i>Choose Your Travel Class</h3>
    <p style="color:var(--text-muted);">Select from a range of travel classes to match your comfort and budget</p>
  </div>

  <div class="row g-4 mb-5">
    <!-- Economy -->
    <div class="col-lg-3 col-md-6">
      <div class="flight-class-card" style="background:var(--bg-card);border:1px solid var(--border-color);border-radius:var(--radius-lg);padding:0;overflow:hidden;transition:all 0.3s;position:relative;" onmouseover="this.style.transform='translateY(-6px)';this.style.boxShadow='0 12px 40px rgba(212,160,23,0.15)'" onmouseout="this.style.transform='';this.style.boxShadow=''">
        <div style="background:linear-gradient(135deg,#2196F3,#1565C0);padding:20px;text-align:center;">
          <i class="fas fa-couch fa-2x mb-2" style="color:rgba(255,255,255,0.9);"></i>
          <h5 class="fw-bold text-white mb-0">Economy</h5>
        </div>
        <div style="padding:24px;">
          <p style="color:var(--text-secondary);font-size:0.88rem;line-height:1.7;">
            Our standard Economy class offers a comfortable and affordable travel experience for everyday flyers. Ideal for short to mid-haul domestic routes.
          </p>
          <ul style="list-style:none;padding:0;margin:0 0 16px;">
            <li style="padding:8px 0;border-bottom:1px solid var(--border-color);color:var(--text-primary);font-size:0.85rem;"><i class="fas fa-check-circle me-2" style="color:#2196F3;"></i>Standard legroom (28-31")</li>
            <li style="padding:8px 0;border-bottom:1px solid var(--border-color);color:var(--text-primary);font-size:0.85rem;"><i class="fas fa-check-circle me-2" style="color:#2196F3;"></i>Complimentary meals</li>
            <li style="padding:8px 0;border-bottom:1px solid var(--border-color);color:var(--text-primary);font-size:0.85rem;"><i class="fas fa-check-circle me-2" style="color:#2196F3;"></i>15 kg checked baggage</li>
            <li style="padding:8px 0;border-bottom:1px solid var(--border-color);color:var(--text-primary);font-size:0.85rem;"><i class="fas fa-check-circle me-2" style="color:#2196F3;"></i>In-flight entertainment</li>
            <li style="padding:8px 0;color:var(--text-primary);font-size:0.85rem;"><i class="fas fa-check-circle me-2" style="color:#2196F3;"></i>Priority boarding available</li>
          </ul>
          <div style="text-align:center;">
            <span style="color:var(--text-muted);font-size:0.8rem;">Starting from</span>
            <div style="font-size:1.6rem;font-weight:800;color:var(--primary);">₹2,999</div>
            <span style="color:var(--text-muted);font-size:0.75rem;">per person, one way</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Premium Economy -->
    <div class="col-lg-3 col-md-6">
      <div class="flight-class-card" style="background:var(--bg-card);border:1px solid var(--border-color);border-radius:var(--radius-lg);padding:0;overflow:hidden;transition:all 0.3s;position:relative;" onmouseover="this.style.transform='translateY(-6px)';this.style.boxShadow='0 12px 40px rgba(212,160,23,0.15)'" onmouseout="this.style.transform='';this.style.boxShadow=''">
        <div style="background:linear-gradient(135deg,#9C27B0,#6A1B9A);padding:20px;text-align:center;">
          <i class="fas fa-star fa-2x mb-2" style="color:rgba(255,255,255,0.9);"></i>
          <h5 class="fw-bold text-white mb-0">Premium Economy</h5>
        </div>
        <div style="padding:24px;">
          <p style="color:var(--text-secondary);font-size:0.88rem;line-height:1.7;">
            Upgrade your journey with extra comfort and enhanced services. Perfect for longer domestic flights when you want a little more space.
          </p>
          <ul style="list-style:none;padding:0;margin:0 0 16px;">
            <li style="padding:8px 0;border-bottom:1px solid var(--border-color);color:var(--text-primary);font-size:0.85rem;"><i class="fas fa-check-circle me-2" style="color:#9C27B0;"></i>Extra legroom (32-34")</li>
            <li style="padding:8px 0;border-bottom:1px solid var(--border-color);color:var(--text-primary);font-size:0.85rem;"><i class="fas fa-check-circle me-2" style="color:#9C27B0;"></i>Premium meal service</li>
            <li style="padding:8px 0;border-bottom:1px solid var(--border-color);color:var(--text-primary);font-size:0.85rem;"><i class="fas fa-check-circle me-2" style="color:#9C27B0;"></i>25 kg checked baggage</li>
            <li style="padding:8px 0;border-bottom:1px solid var(--border-color);color:var(--text-primary);font-size:0.85rem;"><i class="fas fa-check-circle me-2" style="color:#9C27B0;"></i>Wider reclining seats</li>
            <li style="padding:8px 0;color:var(--text-primary);font-size:0.85rem;"><i class="fas fa-check-circle me-2" style="color:#9C27B0;"></i>Priority check-in & boarding</li>
          </ul>
          <div style="text-align:center;">
            <span style="color:var(--text-muted);font-size:0.8rem;">Starting from</span>
            <div style="font-size:1.6rem;font-weight:800;color:var(--primary);">₹5,499</div>
            <span style="color:var(--text-muted);font-size:0.75rem;">per person, one way</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Business -->
    <div class="col-lg-3 col-md-6">
      <div class="flight-class-card" style="background:var(--bg-card);border:2px solid var(--primary);border-radius:var(--radius-lg);padding:0;overflow:hidden;transition:all 0.3s;position:relative;" onmouseover="this.style.transform='translateY(-6px)';this.style.boxShadow='0 12px 40px rgba(212,160,23,0.25)'" onmouseout="this.style.transform='';this.style.boxShadow='0 0 0 2px var(--primary)'">
        <div style="position:absolute;top:12px;right:12px;background:var(--gradient-primary);color:#fff;padding:3px 12px;border-radius:20px;font-size:0.7rem;font-weight:700;z-index:1;">POPULAR</div>
        <div style="background:linear-gradient(135deg,var(--primary),var(--primary-dark));padding:20px;text-align:center;">
          <i class="fas fa-gem fa-2x mb-2" style="color:rgba(255,255,255,0.9);"></i>
          <h5 class="fw-bold text-white mb-0">Business Class</h5>
        </div>
        <div style="padding:24px;">
          <p style="color:var(--text-secondary);font-size:0.88rem;line-height:1.7;">
            Experience luxury travel with premium lie-flat seats, gourmet dining, and exclusive lounge access. The ultimate in-flight experience.
          </p>
          <ul style="list-style:none;padding:0;margin:0 0 16px;">
            <li style="padding:8px 0;border-bottom:1px solid var(--border-color);color:var(--text-primary);font-size:0.85rem;"><i class="fas fa-check-circle me-2" style="color:var(--primary);"></i>Lie-flat seats (up to 6')</li>
            <li style="padding:8px 0;border-bottom:1px solid var(--border-color);color:var(--text-primary);font-size:0.85rem;"><i class="fas fa-check-circle me-2" style="color:var(--primary);"></i>Multi-course gourmet meals</li>
            <li style="padding:8px 0;border-bottom:1px solid var(--border-color);color:var(--text-primary);font-size:0.85rem;"><i class="fas fa-check-circle me-2" style="color:var(--primary);"></i>40 kg checked baggage</li>
            <li style="padding:8px 0;border-bottom:1px solid var(--border-color);color:var(--text-primary);font-size:0.85rem;"><i class="fas fa-check-circle me-2" style="color:var(--primary);"></i>Airport lounge access</li>
            <li style="padding:8px 0;color:var(--text-primary);font-size:0.85rem;"><i class="fas fa-check-circle me-2" style="color:var(--primary);"></i>Priority baggage & check-in</li>
          </ul>
          <div style="text-align:center;">
            <span style="color:var(--text-muted);font-size:0.8rem;">Starting from</span>
            <div style="font-size:1.6rem;font-weight:800;color:var(--primary);">₹14,999</div>
            <span style="color:var(--text-muted);font-size:0.75rem;">per person, one way</span>
          </div>
        </div>
      </div>
    </div>

    <!-- First Class -->
    <div class="col-lg-3 col-md-6">
      <div class="flight-class-card" style="background:var(--bg-card);border:1px solid var(--border-color);border-radius:var(--radius-lg);padding:0;overflow:hidden;transition:all 0.3s;position:relative;" onmouseover="this.style.transform='translateY(-6px)';this.style.boxShadow='0 12px 40px rgba(212,160,23,0.15)'" onmouseout="this.style.transform='';this.style.boxShadow=''">
        <div style="background:linear-gradient(135deg,#FFD700,#FF8C00);padding:20px;text-align:center;">
          <i class="fas fa-crown fa-2x mb-2" style="color:rgba(255,255,255,0.9);"></i>
          <h5 class="fw-bold text-white mb-0">First Class</h5>
        </div>
        <div style="padding:24px;">
          <p style="color:var(--text-secondary);font-size:0.88rem;line-height:1.7;">
            The pinnacle of air travel. Private suites, personal attendants, and world-class amenities for those who demand nothing but the best.
          </p>
          <ul style="list-style:none;padding:0;margin:0 0 16px;">
            <li style="padding:8px 0;border-bottom:1px solid var(--border-color);color:var(--text-primary);font-size:0.85rem;"><i class="fas fa-check-circle me-2" style="color:#FF8C00;"></i>Private enclosed suite</li>
            <li style="padding:8px 0;border-bottom:1px solid var(--border-color);color:var(--text-primary);font-size:0.85rem;"><i class="fas fa-check-circle me-2" style="color:#FF8C00;"></i>Chef-curated dining</li>
            <li style="padding:8px 0;border-bottom:1px solid var(--border-color);color:var(--text-primary);font-size:0.85rem;"><i class="fas fa-check-circle me-2" style="color:#FF8C00;"></i>50 kg checked baggage</li>
            <li style="padding:8px 0;border-bottom:1px solid var(--border-color);color:var(--text-primary);font-size:0.85rem;"><i class="fas fa-check-circle me-2" style="color:#FF8C00;"></i>Luxury amenity kit & pajamas</li>
            <li style="padding:8px 0;color:var(--text-primary);font-size:0.85rem;"><i class="fas fa-check-circle me-2" style="color:#FF8C00;"></i>Chauffeur service to airport</li>
          </ul>
          <div style="text-align:center;">
            <span style="color:var(--text-muted);font-size:0.8rem;">Starting from</span>
            <div style="font-size:1.6rem;font-weight:800;color:var(--primary);">₹29,999</div>
            <span style="color:var(--text-muted);font-size:0.75rem;">per person, one way</span>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Class Comparison Table -->
  <div class="mb-5" style="background:var(--bg-card);border:1px solid var(--border-color);border-radius:var(--radius-lg);overflow:hidden;">
    <div style="background:var(--gradient-primary);padding:18px 30px;">
      <h5 class="fw-bold mb-0 text-white"><i class="fas fa-balance-scale me-2"></i>Class Comparison</h5>
    </div>
    <div class="table-responsive">
      <table class="table table-hover mb-0" style="color:var(--text-primary);">
        <thead style="background:var(--bg-secondary);">
          <tr>
            <th style="border-color:var(--border-color);font-weight:600;">Feature</th>
            <th style="border-color:var(--border-color);font-weight:600;text-align:center;color:#2196F3;">Economy</th>
            <th style="border-color:var(--border-color);font-weight:600;text-align:center;color:#9C27B0;">Premium Economy</th>
            <th style="border-color:var(--border-color);font-weight:600;text-align:center;color:var(--primary);">Business</th>
            <th style="border-color:var(--border-color);font-weight:600;text-align:center;color:#FF8C00;">First Class</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td style="border-color:var(--border-color);font-weight:500;">Seat Type</td>
            <td style="border-color:var(--border-color);text-align:center;">Standard</td>
            <td style="border-color:var(--border-color);text-align:center;">Wider Recliner</td>
            <td style="border-color:var(--border-color);text-align:center;">Lie-Flat Bed</td>
            <td style="border-color:var(--border-color);text-align:center;">Private Suite</td>
          </tr>
          <tr>
            <td style="border-color:var(--border-color);font-weight:500;">Legroom</td>
            <td style="border-color:var(--border-color);text-align:center;">28-31 inches</td>
            <td style="border-color:var(--border-color);text-align:center;">32-34 inches</td>
            <td style="border-color:var(--border-color);text-align:center;">Up to 6 feet</td>
            <td style="border-color:var(--border-color);text-align:center;">Full-length bed</td>
          </tr>
          <tr>
            <td style="border-color:var(--border-color);font-weight:500;">Baggage Allowance</td>
            <td style="border-color:var(--border-color);text-align:center;">15 kg</td>
            <td style="border-color:var(--border-color);text-align:center;">25 kg</td>
            <td style="border-color:var(--border-color);text-align:center;">40 kg</td>
            <td style="border-color:var(--border-color);text-align:center;">50 kg</td>
          </tr>
          <tr>
            <td style="border-color:var(--border-color);font-weight:500;">Meals</td>
            <td style="border-color:var(--border-color);text-align:center;">Complimentary</td>
            <td style="border-color:var(--border-color);text-align:center;">Premium Menu</td>
            <td style="border-color:var(--border-color);text-align:center;">Multi-course Gourmet</td>
            <td style="border-color:var(--border-color);text-align:center;">Chef-curated</td>
          </tr>
          <tr>
            <td style="border-color:var(--border-color);font-weight:500;">Lounge Access</td>
            <td style="border-color:var(--border-color);text-align:center;"><i class="fas fa-times" style="color:#e74c3c;"></i></td>
            <td style="border-color:var(--border-color);text-align:center;"><i class="fas fa-times" style="color:#e74c3c;"></i></td>
            <td style="border-color:var(--border-color);text-align:center;"><i class="fas fa-check" style="color:#27ae60;"></i></td>
            <td style="border-color:var(--border-color);text-align:center;"><i class="fas fa-check" style="color:#27ae60;"></i></td>
          </tr>
          <tr>
            <td style="border-color:var(--border-color);font-weight:500;">Priority Boarding</td>
            <td style="border-color:var(--border-color);text-align:center;">Paid Add-on</td>
            <td style="border-color:var(--border-color);text-align:center;"><i class="fas fa-check" style="color:#27ae60;"></i></td>
            <td style="border-color:var(--border-color);text-align:center;"><i class="fas fa-check" style="color:#27ae60;"></i></td>
            <td style="border-color:var(--border-color);text-align:center;"><i class="fas fa-check" style="color:#27ae60;"></i></td>
          </tr>
          <tr>
            <td style="border-color:var(--border-color);font-weight:500;">Wi-Fi</td>
            <td style="border-color:var(--border-color);text-align:center;">Paid</td>
            <td style="border-color:var(--border-color);text-align:center;">Free (basic)</td>
            <td style="border-color:var(--border-color);text-align:center;">Free (high-speed)</td>
            <td style="border-color:var(--border-color);text-align:center;">Free (unlimited)</td>
          </tr>
          <tr>
            <td style="border-color:var(--border-color);font-weight:500;">Chauffeur Service</td>
            <td style="border-color:var(--border-color);text-align:center;"><i class="fas fa-times" style="color:#e74c3c;"></i></td>
            <td style="border-color:var(--border-color);text-align:center;"><i class="fas fa-times" style="color:#e74c3c;"></i></td>
            <td style="border-color:var(--border-color);text-align:center;"><i class="fas fa-times" style="color:#e74c3c;"></i></td>
            <td style="border-color:var(--border-color);text-align:center;"><i class="fas fa-check" style="color:#27ae60;"></i></td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Flight Booking Form -->
  <div class="flight-form-card">
    <div class="flight-form-header">
      <i class="fas fa-plane"></i>
      <h3>Flight Search & Booking</h3>
    </div>
    <div class="p-4 p-md-5">

      <div id="flightAlert"></div>

      <form id="flightForm" onsubmit="return false;">

        <!-- Trip Type -->
        <div class="mb-4">
          <div class="trip-type-tabs">
            <input type="radio" name="trip_type" id="oneway" value="oneway" checked onchange="toggleReturn()">
            <label for="oneway"><i class="fas fa-arrow-right me-1"></i> One Way</label>
            <input type="radio" name="trip_type" id="roundtrip" value="roundtrip" onchange="toggleReturn()">
            <label for="roundtrip"><i class="fas fa-exchange-alt me-1"></i> Round Trip</label>
          </div>
        </div>

        <!-- From / To -->
        <div class="row g-3 mb-3 align-items-start">
          <div class="col-md-5">
            <div class="form-floating">
              <select class="form-select" id="from" name="from" required>
                <option value="" disabled selected>Select Origin</option>
                <option value="Delhi (DEL)">Delhi (DEL)</option>
                <option value="Mumbai (BOM)">Mumbai (BOM)</option>
                <option value="Bangalore (BLR)">Bangalore (BLR)</option>
                <option value="Chennai (MAA)">Chennai (MAA)</option>
                <option value="Kolkata (CCU)">Kolkata (CCU)</option>
                <option value="Hyderabad (HYD)">Hyderabad (HYD)</option>
                <option value="Pune (PNQ)">Pune (PNQ)</option>
                <option value="Ahmedabad (AMD)">Ahmedabad (AMD)</option>
                <option value="Goa (GOI)">Goa (GOI)</option>
                <option value="Jaipur (JAI)">Jaipur (JAI)</option>
                <option value="Lucknow (LKO)">Lucknow (LKO)</option>
                <option value="Kochi (COK)">Kochi (COK)</option>
                <option value="Colombo (CMB)">Colombo (CMB)</option>
                <option value="Dubai (DXB)">Dubai (DXB)</option>
                <option value="Singapore (SIN)">Singapore (SIN)</option>
                <option value="Bangkok (BKK)">Bangkok (BKK)</option>
                <option value="London (LHR)">London (LHR)</option>
                <option value="New York (JFK)">New York (JFK)</option>
              </select>
              <label for="from"><i class="fas fa-plane-departure me-1"></i> From</label>
            </div>
          </div>

          <div class="col-md-2 d-flex justify-content-center">
            <button type="button" class="swap-btn" onclick="swapCities()" title="Swap cities">
              <i class="fas fa-exchange-alt"></i>
            </button>
          </div>

          <div class="col-md-5">
            <div class="form-floating">
              <select class="form-select" id="to" name="to" required>
                <option value="" disabled selected>Select Destination</option>
                <option value="Delhi (DEL)">Delhi (DEL)</option>
                <option value="Mumbai (BOM)">Mumbai (BOM)</option>
                <option value="Bangalore (BLR)">Bangalore (BLR)</option>
                <option value="Chennai (MAA)">Chennai (MAA)</option>
                <option value="Kolkata (CCU)">Kolkata (CCU)</option>
                <option value="Hyderabad (HYD)">Hyderabad (HYD)</option>
                <option value="Pune (PNQ)">Pune (PNQ)</option>
                <option value="Ahmedabad (AMD)">Ahmedabad (AMD)</option>
                <option value="Goa (GOI)">Goa (GOI)</option>
                <option value="Jaipur (JAI)">Jaipur (JAI)</option>
                <option value="Lucknow (LKO)">Lucknow (LKO)</option>
                <option value="Kochi (COK)">Kochi (COK)</option>
                <option value="Colombo (CMB)">Colombo (CMB)</option>
                <option value="Dubai (DXB)">Dubai (DXB)</option>
                <option value="Singapore (SIN)">Singapore (SIN)</option>
                <option value="Bangkok (BKK)">Bangkok (BKK)</option>
                <option value="London (LHR)">London (LHR)</option>
                <option value="New York (JFK)">New York (JFK)</option>
              </select>
              <label for="to"><i class="fas fa-plane-arrival me-1"></i> To</label>
            </div>
          </div>
        </div>

        <!-- Dates / Travelers / Class -->
        <div class="row g-3 mb-4">
          <div class="col-md-3">
            <div class="form-floating">
              <input type="date" class="form-control" id="departure_date" name="departure_date" required min="<?= date('Y-m-d') ?>">
              <label for="departure_date"><i class="fas fa-calendar-alt me-1"></i> Departure</label>
            </div>
          </div>
          <div class="col-md-3" id="returnCol" style="display:none;">
            <div class="form-floating">
              <input type="date" class="form-control" id="return_date" name="return_date" min="<?= date('Y-m-d') ?>">
              <label for="return_date"><i class="fas fa-calendar-check me-1"></i> Return</label>
            </div>
          </div>
          <div class="col-md-3">
            <div class="form-floating">
              <select class="form-select" id="travelers" name="travelers">
                <?php for ($i = 1; $i <= 9; $i++): ?>
                <option value="<?= $i ?>"><?= $i ?> <?= $i === 1 ? 'Traveler' : 'Travelers' ?></option>
                <?php endfor; ?>
              </select>
              <label for="travelers"><i class="fas fa-users me-1"></i> Travelers</label>
            </div>
          </div>
          <div class="col-md-3">
            <div class="form-floating">
              <select class="form-select" id="travel_class" name="travel_class">
                <option value="economy">Economy</option>
                <option value="premium_economy">Premium Economy</option>
                <option value="business">Business</option>
                <option value="first">First Class</option>
              </select>
              <label for="travel_class"><i class="fas fa-couch me-1"></i> Class</label>
            </div>
          </div>
        </div>

        <hr style="border-color:var(--border-color);">

        <h5 class="fw-bold mb-3" style="color:var(--text-heading);"><i class="fas fa-user me-2" style="color:var(--primary);"></i>Contact Details</h5>

        <div class="row g-3 mb-4">
          <div class="col-md-4">
            <div class="form-floating">
              <input type="text" class="form-control" id="full_name" name="full_name" placeholder="Full Name" required value="<?= htmlspecialchars($current_user['name'] ?? '') ?>">
              <label for="full_name">Full Name</label>
            </div>
          </div>
          <div class="col-md-4">
            <div class="form-floating">
              <input type="email" class="form-control" id="email" name="email" placeholder="Email" required value="<?= htmlspecialchars($current_user['email'] ?? '') ?>">
              <label for="email">Email Address</label>
            </div>
          </div>
          <div class="col-md-4">
            <div class="form-floating">
              <input type="tel" class="form-control" id="phone" name="phone" placeholder="Phone" value="<?= htmlspecialchars($current_user['phone'] ?? '') ?>">
              <label for="phone">Phone Number</label>
            </div>
          </div>
        </div>

        <hr style="border-color:var(--border-color);">

        <h5 class="fw-bold mb-3" style="color:var(--text-heading);"><i class="fas fa-credit-card me-2" style="color:var(--primary);"></i>Payment Method</h5>

        <div class="row g-3 mb-4">
          <div class="col-md-6">
            <div class="flight-pay-option selected" onclick="selectPayMethod(this, 'razorpay')" style="border:2px solid var(--primary);border-radius:var(--radius-md);padding:16px 20px;cursor:pointer;background:rgba(212,160,23,0.06);transition:all 0.2s;">
              <div class="d-flex align-items-center gap-3">
                <i class="fas fa-shield-alt fa-lg" style="color:var(--primary);"></i>
                <div>
                  <div class="fw-bold" style="color:var(--text-heading);font-size:0.95rem;">Pay Online</div>
                  <small style="color:var(--text-muted);">Razorpay — UPI, Cards, Netbanking, Wallets</small>
                </div>
              </div>
            </div>
          </div>
          <div class="col-md-6">
            <div class="flight-pay-option" onclick="selectPayMethod(this, 'cod')" style="border:2px solid var(--border-color);border-radius:var(--radius-md);padding:16px 20px;cursor:pointer;transition:all 0.2s;">
              <div class="d-flex align-items-center gap-3">
                <i class="fas fa-money-bill-wave fa-lg" style="color:var(--success);"></i>
                <div>
                  <div class="fw-bold" style="color:var(--text-heading);font-size:0.95rem;">Pay on Departure</div>
                  <small style="color:var(--text-muted);">Book now, pay at the airport counter</small>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div id="fareSummary" class="mb-4" style="background:var(--bg-secondary);border-radius:var(--radius-md);padding:20px;display:none;">
          <h6 class="fw-bold mb-3" style="color:var(--text-heading);"><i class="fas fa-receipt me-2" style="color:var(--primary);"></i>Fare Summary</h6>
          <div id="fareBreakdown" style="color:var(--text-secondary);font-size:0.9rem;"></div>
          <hr style="border-color:var(--border-color);margin:10px 0;">
          <div class="d-flex justify-content-between align-items-center">
            <span class="fw-bold" style="color:var(--text-heading);">Total</span>
            <span id="fareTotal" class="fw-bold" style="color:var(--primary);font-size:1.4rem;"></span>
          </div>
        </div>

        <div class="text-center">
          <button type="button" class="btn btn-book" onclick="submitBooking()">
            <i class="fas fa-paper-plane me-2"></i><span id="bookBtnText">Proceed to Payment</span>
          </button>
          <div id="bookSpinner" class="mt-3" style="display:none;">
            <div class="spinner-border text-warning" role="status"></div>
            <span class="ms-2" style="color:var(--text-muted);font-size:0.9rem;">Processing your booking...</span>
          </div>
        </div>
      </form>
    </div>
  </div>

  <script>
var selectedPayMethod = 'razorpay';

var flightPrices = {
  economy: 2999,
  premium_economy: 5499,
  business: 14999,
  first: 29999
};

var classLabels = {
  economy: 'Economy',
  premium_economy: 'Premium Economy',
  business: 'Business Class',
  first: 'First Class'
};

function swapCities() {
  var fromEl = document.getElementById('from');
  var toEl = document.getElementById('to');
  var temp = fromEl.value;
  fromEl.value = toEl.value;
  toEl.value = temp;
}

function toggleReturn() {
  var isRound = document.getElementById('roundtrip').checked;
  document.getElementById('returnCol').style.display = isRound ? '' : 'none';
  if (!isRound) document.getElementById('return_date').value = '';
  updateFare();
}

function selectPayMethod(el, method) {
  selectedPayMethod = method;
  document.querySelectorAll('.flight-pay-option').forEach(function(o) {
    o.style.borderColor = 'var(--border-color)';
    o.style.background = '';
  });
  el.style.borderColor = 'var(--primary)';
  el.style.background = 'rgba(212,160,23,0.06)';
  var btn = document.getElementById('bookBtnText');
  btn.textContent = method === 'cod' ? 'Confirm Booking' : 'Proceed to Payment';
}

function updateFare() {
  var travelClass = document.getElementById('travel_class').value;
  var travelers = parseInt(document.getElementById('travelers').value) || 1;
  var isRound = document.getElementById('roundtrip').checked;
  var unitPrice = flightPrices[travelClass] || 2999;
  var subtotal = unitPrice * travelers;
  if (isRound) subtotal *= 2;

  var summary = document.getElementById('fareSummary');
  var breakdown = document.getElementById('fareBreakdown');
  var total = document.getElementById('fareTotal');

  summary.style.display = 'block';
  breakdown.innerHTML =
    '<div class="d-flex justify-content-between mb-1"><span>' + classLabels[travelClass] + ' × ' + travelers + ' traveler' + (travelers > 1 ? 's' : '') + (isRound ? ' × 2 (round trip)' : '') + '</span><span>₹' + unitPrice.toLocaleString('en-IN') + (travelers > 1 ? ' × ' + travelers + (isRound ? ' × 2' : '') : '') + '</span></div>';
  total.textContent = '₹' + subtotal.toLocaleString('en-IN');
}

document.getElementById('travel_class').addEventListener('change', updateFare);
document.getElementById('travelers').addEventListener('change', updateFare);
updateFare();

function showAlert(type, message) {
  var el = document.getElementById('flightAlert');
  var cls = type === 'error' ? 'danger' : 'success';
  el.innerHTML = '<div class="alert alert-' + cls + ' alert-dismissible fade show"><i class="fas fa-' + (type === 'error' ? 'exclamation-circle' : 'check-circle') + ' me-2"></i>' + message + '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
}

function validateForm() {
  var from = document.getElementById('from').value;
  var to = document.getElementById('to').value;
  var depart = document.getElementById('departure_date').value;
  var name = document.getElementById('full_name').value.trim();
  var email = document.getElementById('email').value.trim();
  var isRound = document.getElementById('roundtrip').checked;
  var ret = document.getElementById('return_date').value;

  if (!from || !to) { showAlert('error', 'Please select origin and destination.'); return false; }
  if (from === to) { showAlert('error', 'Origin and destination cannot be the same.'); return false; }
  if (!depart) { showAlert('error', 'Please select a departure date.'); return false; }
  if (isRound && !ret) { showAlert('error', 'Please select a return date for round trip.'); return false; }
  if (isRound && ret && ret < depart) { showAlert('error', 'Return date cannot be before departure date.'); return false; }
  if (!name) { showAlert('error', 'Please enter your full name.'); return false; }
  if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) { showAlert('error', 'Please enter a valid email address.'); return false; }
  return true;
}

function submitBooking() {
  if (!validateForm()) return;
  if (selectedPayMethod === 'cod') {
    bookCOD();
  } else {
    bookOnline();
  }
}

function bookCOD() {
  var btn = document.getElementById('bookBtnText');
  var spinner = document.getElementById('bookSpinner');
  btn.textContent = 'Booking...';
  spinner.style.display = 'block';

  var data = new FormData();
  data.append('action', 'cod_booking');
  data.append('from', document.getElementById('from').value);
  data.append('to', document.getElementById('to').value);
  data.append('departure_date', document.getElementById('departure_date').value);
  data.append('return_date', document.getElementById('return_date').value);
  data.append('trip_type', document.querySelector('input[name="trip_type"]:checked').value);
  data.append('travelers', document.getElementById('travelers').value);
  data.append('travel_class', document.getElementById('travel_class').value);
  data.append('full_name', document.getElementById('full_name').value.trim());
  data.append('email', document.getElementById('email').value.trim());
  data.append('phone', document.getElementById('phone').value.trim());

  fetch('ajax/flight_payment.php', { method: 'POST', body: data })
    .then(function(r) { return r.json(); })
    .then(function(res) {
      btn.textContent = 'Confirm Booking';
      spinner.style.display = 'none';
      if (res.success && res.redirect) {
        showAlert('success', res.message);
        setTimeout(function() { window.location.href = res.redirect; }, 1000);
      } else {
        showAlert('error', res.message || 'Booking failed.');
      }
    })
    .catch(function() {
      btn.textContent = 'Confirm Booking';
      spinner.style.display = 'none';
      showAlert('error', 'Network error. Please try again.');
    });
}

function bookOnline() {
  var btn = document.getElementById('bookBtnText');
  var spinner = document.getElementById('bookSpinner');
  btn.textContent = 'Creating order...';
  spinner.style.display = 'block';

  var data = new FormData();
  data.append('action', 'create_order');
  data.append('from', document.getElementById('from').value);
  data.append('to', document.getElementById('to').value);
  data.append('departure_date', document.getElementById('departure_date').value);
  data.append('return_date', document.getElementById('return_date').value);
  data.append('trip_type', document.querySelector('input[name="trip_type"]:checked').value);
  data.append('travelers', document.getElementById('travelers').value);
  data.append('travel_class', document.getElementById('travel_class').value);
  data.append('full_name', document.getElementById('full_name').value.trim());
  data.append('email', document.getElementById('email').value.trim());
  data.append('phone', document.getElementById('phone').value.trim());

  fetch('ajax/flight_payment.php', { method: 'POST', body: data })
    .then(function(r) { return r.json(); })
    .then(function(res) {
      if (!res.success) {
        btn.textContent = 'Proceed to Payment';
        spinner.style.display = 'none';
        showAlert('error', res.message || 'Failed to initiate payment.');
        return;
      }

      var options = {
        key: res.key,
        amount: res.amount,
        currency: res.currency,
        name: res.name,
        description: res.description,
        order_id: res.razorpay_order_id,
        handler: function(response) {
          btn.textContent = 'Verifying payment...';
          var vdata = new FormData();
          vdata.append('action', 'verify_payment');
          vdata.append('razorpay_order_id', response.razorpay_order_id);
          vdata.append('razorpay_payment_id', response.razorpay_payment_id);
          vdata.append('razorpay_signature', response.razorpay_signature);

          fetch('ajax/flight_payment.php', { method: 'POST', body: vdata })
            .then(function(r) { return r.json(); })
            .then(function(vres) {
              btn.textContent = 'Proceed to Payment';
              spinner.style.display = 'none';
              if (vres.success && vres.redirect) {
                showAlert('success', vres.message);
                setTimeout(function() { window.location.href = vres.redirect; }, 1200);
              } else {
                showAlert('error', vres.message || 'Payment verification failed.');
              }
            })
            .catch(function() {
              btn.textContent = 'Proceed to Payment';
              spinner.style.display = 'none';
              showAlert('error', 'Network error during verification. Payment ID: ' + response.razorpay_payment_id);
            });
        },
        prefill: res.prefill,
        theme: { color: '#D4A017' },
        modal: {
          ondismiss: function() {
            btn.textContent = 'Proceed to Payment';
            spinner.style.display = 'none';
            showAlert('error', 'Payment cancelled.');
          }
        }
      };

      var rzp = new Razorpay(options);
      rzp.on('payment.failed', function(response) {
        btn.textContent = 'Proceed to Payment';
        spinner.style.display = 'none';
        showAlert('error', 'Payment failed. Please try again. (Error: ' + (response.error.description || 'Unknown') + ')');
      });
      rzp.open();
    })
    .catch(function() {
      btn.textContent = 'Proceed to Payment';
      spinner.style.display = 'none';
      showAlert('error', 'Network error. Please try again.');
    });
}
</script>

<?php require_once 'includes/footer.php'; ?>
