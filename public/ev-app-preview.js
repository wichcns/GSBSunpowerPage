(() => {
  const image = document.querySelector('.nrg-hero-app-device img') || document.querySelector('.nrg-phone-main');
  const choices = document.querySelectorAll('.nrg-phone-choice');

  if (!image || !choices.length) return;

  let currentIndex = [...choices].findIndex((choice) => choice.classList.contains('is-active'));
  const showScreen = (choice) => {
    const source = choice?.dataset.appScreen;
    if (!source) return;

    image.classList.add('is-switching');
    window.setTimeout(() => {
      image.src = source;
      image.classList.remove('is-switching');
    }, 120);

    choices.forEach((button) => button.classList.toggle('is-active', button === choice));
  };

  choices.forEach((choice, index) => {
    choice.addEventListener('click', () => {
      currentIndex = index;
      showScreen(choice);
    });
  });

  window.setInterval(() => {
    currentIndex = (currentIndex + 1) % choices.length;
    showScreen(choices[currentIndex]);
  }, 3500);
})();
