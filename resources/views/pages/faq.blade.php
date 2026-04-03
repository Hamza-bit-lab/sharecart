<x-app-layout>
    <div class="row justify-content-center">
        <div class="col-lg-10 col-xl-8">
            <h1 class="hero-title text-center mb-5" style="font-size: 2.5rem;">
                Frequently Asked <span>Questions</span>
            </h1>
            
            <div class="sharecart-card">
                <div class="card-body p-4 p-md-5">
                    
                    <!-- FAQ Item 1 -->
                    <div class="mb-5 pb-4 border-bottom" style="border-color: var(--sc-border) !important;">
                        <h4 class="fw-bold text-dark mb-3">What is ShareCart?</h4>
                        <p class="text-muted mb-0">ShareCart is a collaborative grocery list application designed to make shopping easier. You can create lists, add items, categorize them, and share the lists with family members, roommates, or friends so everyone can contribute and check off items in real-time.</p>
                    </div>

                    <!-- FAQ Item 2 -->
                    <div class="mb-5 pb-4 border-bottom" style="border-color: var(--sc-border) !important;">
                        <h4 class="fw-bold text-dark mb-3">How do I share a list with someone?</h4>
                        <p class="text-muted mb-3">There are three main ways to share a list:</p>
                        <ul class="list-unstyled d-flex flex-column gap-3 mb-0 text-muted">
                            <li class="d-flex gap-3">
                                <i class="bi bi-envelope text-primary mt-1"></i>
                                <div><strong>Email Invitation:</strong> If the person has a ShareCart account, go to the list settings and share it via their email address.</div>
                            </li>
                            <li class="d-flex gap-3">
                                <i class="bi bi-link-45deg text-primary mt-1"></i>
                                <div><strong>Invite Link:</strong> Generate a unique invite link from the list and send it to anyone.</div>
                            </li>
                            <li class="d-flex gap-3">
                                <i class="bi bi-123 text-primary mt-1"></i>
                                <div><strong>Join Code:</strong> Every list has a 5-digit alphanumeric code. Others can enter this code on the "Join" page to access the list, even without an account (as guests).</div>
                            </li>
                        </ul>
                    </div>

                    <!-- FAQ Item 3 -->
                    <div class="mb-5 pb-4 border-bottom" style="border-color: var(--sc-border) !important;">
                        <h4 class="fw-bold text-dark mb-3">What does "Claim Item" mean?</h4>
                        <p class="text-muted mb-0">When multiple people are shopping for the same list at different stores, or if someone wants to specifically take responsibility for picking up a certain item, they can click "Claim Item." This tells other collaborators that someone is already planning to get that specific item, preventing duplicate purchases.</p>
                    </div>

                    <!-- FAQ Item 4 -->
                    <div class="mb-5 pb-4 border-bottom" style="border-color: var(--sc-border) !important;">
                        <h4 class="fw-bold text-dark mb-3">What does the "Nudge!" button do?</h4>
                        <p class="text-muted mb-0">The "Nudge" feature sends a push notification to all other collaborators on the list. It's a quick way to say, "Hey, I'm at the store right now! Add anything you need to the list quickly."</p>
                    </div>

                    <!-- FAQ Item 5 -->
                    <div class="mb-5 pb-4 border-bottom" style="border-color: var(--sc-border) !important;">
                        <h4 class="fw-bold text-dark mb-3">How does the "Settlement" or shared payments feature work?</h4>
                        <p class="text-muted mb-0">As you check off items, you can log payment amounts associated with the list. ShareCart's settlement feature calculates the total amount spent by everyone and determines the "Fair Share" (average cost per person). It then shows the balances—who owes money and who is owed—to easily settle up after shopping.</p>
                    </div>
                    
                    <!-- FAQ Item 6 -->
                    <div class="mb-0">
                        <h4 class="fw-bold text-dark mb-3">Do I need an account to use ShareCart?</h4>
                        <p class="text-muted mb-0">You need an account to create and manage your own lists. However, if someone shares a 5-digit "Join Code" with you, you can join and edit that specific list as a "Guest" without needing to register or log in.</p>
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
