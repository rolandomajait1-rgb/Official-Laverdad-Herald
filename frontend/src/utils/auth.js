// Auth utility functions
export const isAdmin = () => {
    const token = localStorage.getItem('auth_token');
    const userRole = localStorage.getItem('user_role');
    return token && userRole === 'admin';
};

export const isModerator = () => {
    const token = localStorage.getItem('auth_token');
    const userRole = localStorage.getItem('user_role');
    return token && userRole === 'moderator';
};

export const getAuthToken = () => {
    return localStorage.getItem('auth_token');
};

export const getUserRole = () => {
    return localStorage.getItem('user_role');
};

// Article management functions
export const editArticle = (articleId) => {
    window.location.href = `/admin/edit-article/${articleId}`;
};

export const deleteArticle = async(articleId) => {
    if (!window.confirm('Are you sure you want to delete this article?')) {
        return false;
    }

    try {
        const token = getAuthToken();
        const response = await fetch(`http://localhost:8000/api/articles/${articleId}`, {
            method: 'DELETE',
            headers: {
                'Authorization': `Bearer ${token}`,
                'Content-Type': 'application/json'
            }
        });

        if (response.ok) {
            alert('Article deleted successfully');
            window.location.reload();
            return true;
        } else {
            alert('Failed to delete article');
            return false;
        }
    } catch (error) {
        console.error('Error deleting article:', error);
        alert('Error deleting article');
        return false;
    }
};