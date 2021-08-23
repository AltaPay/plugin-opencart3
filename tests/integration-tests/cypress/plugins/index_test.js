cypress/plugins/index.js
export default (on, config) => {
    // inside config.browsers array each object has information like
    {
      name: "chrome"
      family: "chromium"
      channel: 'canary'
      displayName: 'Chromium'
      version: '92.0.4512.0'
      path:
       '‪C:/Program Files/Google/Chrome/Application/chrome.exe'
      majorVersion: 80
    }
    return {
      browsers: config.browsers.filter((b) => b.family === 'chromium'),
    }
  }