document.addEventListener("DOMContentLoaded", () => {
  // Mobile menu toggle
  const menuToggle = document.querySelector(".menu-toggle")
  const nav = document.querySelector("nav")

  if (menuToggle) {
    menuToggle.addEventListener("click", () => {
      nav.classList.toggle("active")
    })
  }

  // Close menu when clicking outside
  document.addEventListener("click", (e) => {
    if (nav && nav.classList.contains("active") && !nav.contains(e.target) && !menuToggle.contains(e.target)) {
      nav.classList.remove("active")
    }
  })

  // Show community notification after 30 seconds
  const notification = document.getElementById("community-notification")
  if (notification) {
    setTimeout(() => {
      notification.style.display = "flex"
    }, 30000)

    // Close notification
    const closeBtn = notification.querySelector(".close-notification")
    if (closeBtn) {
      closeBtn.addEventListener("click", () => {
        notification.style.display = "none"
      })
    }
  }

  // Random notification on idle
  let idleTimer
  const resetIdleTimer = () => {
    clearTimeout(idleTimer)
    idleTimer = setTimeout(() => {
      if (notification && window.location.pathname !== "/community.html") {
        notification.style.display = "flex"
      }
    }, 60000) // 1 minute of inactivity
  }

  // Reset timer on user activity
  ;["mousedown", "mousemove", "keypress", "scroll", "touchstart"].forEach((event) => {
    document.addEventListener(event, resetIdleTimer, true)
  })

  // Start the timer
  resetIdleTimer()
})
