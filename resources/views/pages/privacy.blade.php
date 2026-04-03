<x-app-layout>
    <div class="row justify-content-center">
        <div class="col-lg-10 col-xl-8">
            <h1 class="hero-title text-center mb-5" style="font-size: 2.5rem;">
                Privacy <span>Policy</span>
            </h1>
            
            <div class="sharecart-card">
                <div class="card-body p-4 p-md-5">
                    <p class="text-muted mb-5"><strong>Last Updated:</strong> {{ date('F j, Y') }}</p>

                    <p class="fs-5 text-dark mb-4">Welcome to ShareCart! Your privacy is important to us. This Privacy Policy explains how we collect, use, and protect your information when you use our application to manage and share your grocery lists.</p>

                    <h4 class="fw-bold mt-5 mb-3 text-dark">1. Information We Collect</h4>
                    <ul class="list-unstyled d-flex flex-column gap-3 mb-4 text-muted">
                        <li class="d-flex gap-3">
                            <i class="bi bi-person-badge text-primary mt-1"></i>
                            <div><strong>Account Information:</strong> When you register, we collect your name, email address, and password.</div>
                        </li>
                        <li class="d-flex gap-3">
                            <i class="bi bi-list-check text-primary mt-1"></i>
                            <div><strong>List Data:</strong> We store the grocery lists you create, including item names, quantities, categories, due dates, and associated completion or claim statuses.</div>
                        </li>
                        <li class="d-flex gap-3">
                            <i class="bi bi-people text-primary mt-1"></i>
                            <div><strong>Collaborator Data:</strong> If you share a list, we store the associations between your list and the invited users. For guest users joining via a code, we may store a temporary display name.</div>
                        </li>
                        <li class="d-flex gap-3">
                            <i class="bi bi-phone text-primary mt-1"></i>
                            <div><strong>Device Information:</strong> To enable push notifications, we collect device tokens (FCM tokens) associated with your user account or device.</div>
                        </li>
                    </ul>

                    <h4 class="fw-bold mt-5 mb-3 text-dark">2. How We Use Your Information</h4>
                    <p class="text-muted">We use the collected information for the following purposes:</p>
                    <ul class="list-unstyled d-flex flex-column gap-3 mb-4 text-muted">
                        <li class="d-flex align-items-center gap-2"><i class="bi bi-check-circle-fill text-success"></i> To provide, maintain, and improve the ShareCart application functionality.</li>
                        <li class="d-flex align-items-center gap-2"><i class="bi bi-check-circle-fill text-success"></i> To sync grocery lists across devices and with your collaborators in real-time.</li>
                        <li class="d-flex align-items-center gap-2"><i class="bi bi-check-circle-fill text-success"></i> To send push notifications for important list updates.</li>
                        <li class="d-flex align-items-center gap-2"><i class="bi bi-check-circle-fill text-success"></i> To calculate fair share settlements if you track payments within a list.</li>
                    </ul>

                    <h4 class="fw-bold mt-5 mb-3 text-dark">3. Information Sharing and Disclosure</h4>
                    <p class="text-muted mb-4">Your grocery lists are private by default. They are only visible to you and the users you explicitly invite or share a join code with. We do not sell your personal data to third parties. We may disclose information if required by law or to protect our rights.</p>

                    <h4 class="fw-bold mt-5 mb-3 text-dark">4. Data Security</h4>
                    <p class="text-muted mb-4">We implement reasonable security measures to protect your data. However, no method of transmission over the internet or electronic storage is 100% secure. We cannot guarantee absolute security but strive to protect your personal information.</p>

                    <h4 class="fw-bold mt-5 mb-3 text-dark">5. Your Rights</h4>
                    <p class="text-muted mb-4">You have the right to access, update, or delete your account information at any time through the profile settings. You can also delete any grocery lists you own, which will remove access for all associated collaborators.</p>

                    <hr class="my-5 opacity-10">

                    <h4 class="fw-bold mb-3 text-dark">Contact Us</h4>
                    <p class="text-muted mb-0">If you have any questions or suggestions about our Privacy Policy, do not hesitate to contact us at <a href="mailto:support@sharecart.com" class="fw-semibold">support@sharecart.com</a>.</p>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
