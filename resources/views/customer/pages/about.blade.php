@extends('customer.layouts.app')

@section('content')
<!-- Page Header -->
<section class="page-header bg-primary text-white py-5">
    <div class="container">
        <div class="row">
            <div class="col-12 text-center">
                <h1 class="h2 mb-3">About JippyMart</h1>
                <p class="lead mb-0">Your trusted local food and grocery delivery service</p>
            </div>
        </div>
    </div>
</section>

<!-- About Content -->
<section class="py-5">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <div class="text-center mb-5">
                    <img src="{{ asset('images/about-hero.jpg') }}" alt="About JippyMart" class="img-fluid rounded mb-4">
                </div>
                
                <h2 class="h3 mb-4">Our Story</h2>
                <p class="lead">JippyMart was founded with a simple mission: to make food and grocery delivery fast, reliable, and accessible to everyone in India.</p>
                
                <p>We understand that in today's fast-paced world, convenience is key. That's why we've built a platform that connects you with the best local restaurants and grocery stores, bringing fresh food and daily essentials right to your doorstep.</p>
                
                <h3 class="h4 mb-3">What We Do</h3>
                <ul class="list-unstyled">
                    <li class="mb-2"><i class="fas fa-check text-primary me-2"></i> Connect you with local restaurants and grocery stores</li>
                    <li class="mb-2"><i class="fas fa-check text-primary me-2"></i> Provide fast and reliable delivery services</li>
                    <li class="mb-2"><i class="fas fa-check text-primary me-2"></i> Ensure quality and freshness of all products</li>
                    <li class="mb-2"><i class="fas fa-check text-primary me-2"></i> Offer competitive prices and great deals</li>
                    <li class="mb-2"><i class="fas fa-check text-primary me-2"></i> Support local businesses and communities</li>
                </ul>
                
                <h3 class="h4 mb-3">Our Values</h3>
                <div class="row">
                    <div class="col-md-6 mb-4">
                        <div class="card h-100">
                            <div class="card-body text-center">
                                <div class="feature-icon bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                                    <i class="fas fa-heart"></i>
                                </div>
                                <h5>Quality First</h5>
                                <p class="text-muted">We never compromise on quality. Every product and meal is carefully selected to meet our high standards.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-4">
                        <div class="card h-100">
                            <div class="card-body text-center">
                                <div class="feature-icon bg-success text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                                    <i class="fas fa-users"></i>
                                </div>
                                <h5>Community Focus</h5>
                                <p class="text-muted">We support local businesses and help build stronger communities through our platform.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-4">
                        <div class="card h-100">
                            <div class="card-body text-center">
                                <div class="feature-icon bg-warning text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                                    <i class="fas fa-bolt"></i>
                                </div>
                                <h5>Speed & Efficiency</h5>
                                <p class="text-muted">We deliver fast without compromising on quality or safety.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-4">
                        <div class="card h-100">
                            <div class="card-body text-center">
                                <div class="feature-icon bg-info text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                                    <i class="fas fa-shield-alt"></i>
                                </div>
                                <h5>Trust & Safety</h5>
                                <p class="text-muted">Your safety and satisfaction are our top priorities. We ensure secure transactions and safe delivery.</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <h3 class="h4 mb-3">Our Impact</h3>
                <div class="row text-center">
                    <div class="col-md-3 mb-4">
                        <div class="stat-item">
                            <h2 class="text-primary">10K+</h2>
                            <p class="text-muted">Happy Customers</p>
                        </div>
                    </div>
                    <div class="col-md-3 mb-4">
                        <div class="stat-item">
                            <h2 class="text-primary">500+</h2>
                            <p class="text-muted">Partner Restaurants</p>
                        </div>
                    </div>
                    <div class="col-md-3 mb-4">
                        <div class="stat-item">
                            <h2 class="text-primary">50K+</h2>
                            <p class="text-muted">Orders Delivered</p>
                        </div>
                    </div>
                    <div class="col-md-3 mb-4">
                        <div class="stat-item">
                            <h2 class="text-primary">25+</h2>
                            <p class="text-muted">Cities Served</p>
                        </div>
                    </div>
                </div>
                
                <div class="text-center mt-5">
                    <h3 class="h4 mb-3">Ready to Experience JippyMart?</h3>
                    <p class="text-muted mb-4">Join thousands of satisfied customers who trust us for their food and grocery needs.</p>
                    <a href="{{ route('customer.home') }}" class="btn btn-primary btn-lg">Start Ordering Now</a>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
