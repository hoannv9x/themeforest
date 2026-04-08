// plugins/google-maps.client.js
export default defineNuxtPlugin((nuxtApp) => {
  const config = useRuntimeConfig();
  const apiKey = config.public.googleMapsApiKey;

  if (!apiKey) {
    console.warn('Google Maps API Key is not set. Map features may not work.');
    return;
  }

  return new Promise((resolve) => {
    if (window.google && window.google.maps) {
      resolve();
      return;
    }

    const script = document.createElement('script');
    script.src = `https://maps.googleapis.com/maps/api/js?key=${apiKey}&libraries=places&callback=initGoogleMaps`;
    script.async = true;
    script.defer = true;
    document.head.appendChild(script);

    window.initGoogleMaps = () => {
      resolve();
    };
  });
});