import dayjs from 'dayjs';

export const useFormatters = () => {
  const formatDate = (dateString, time = false, format = 'yyyy-MM-dd') => {
    if (!dateString) return '';
    if (time) {
      const datetime = dayjs(dateString).format(format);
      return datetime;
    }
    const date = new Date(dateString);
    const day = String(date.getDate()).padStart(2, '0');
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const year = date.getFullYear();
    return `${day}-${month}-${year}`;
  };

  return {
    formatDate,
  };
};
