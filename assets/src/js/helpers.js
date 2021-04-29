export function device(point) {
  let maxWidth = null;

  if (point == "desktop") {
    maxWidth = "(min-width: 1050px)";
  } else if (point == "mobile") {
    maxWidth = "(max-width: 480px)";
  } else if (point == "mobile") {
    maxWidth = "(max-width: 1050px) and (min-width: 480px)";
  } else if (point == "mobileAndTablet") {
    maxWidth = "(max-width: 1050px)";
  }

  if (maxWidth) {
    const query = window.matchMedia(maxWidth);
    return query.matches;
  }

  return false;
}
